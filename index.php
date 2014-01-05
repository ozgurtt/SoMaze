<?
//common vars and such
require_once "lib/common.php";
date_default_timezone_set('America/Chicago');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_REQUEST["type"])){
	$type = $_REQUEST["type"];
}else{
	$type = "none";
}
if (isset($_SERVER['PHP_AUTH_USER'])){
	//someone is logged in, we don't care who

}else{
	$username = "none";
	$access = 0;
}

switch($type){
	case "play":
		if (!isset($_SESSION['user'])){
			//they aren't logged in, so why are they accessing the account page?
			handleError("notloggedin");
		}
		if (!isset($_REQUEST["id"])){
			//they didn't provide an ID for the game they are trying to access
			handleError("noid");
		}
		$body = str_replace("###HEADING###", "Entry confirmation", $body);
		$puzzle = getDoc($_REQUEST["id"], "puzzles");
		$user = getDoc($_SESSION['user'], "users");
		if ($user->wallet->available < $puzzle->fees->entry){
			handleError("nofunds");
		}
		if (isset($user->games->solver->$_REQUEST['id'])){
			//this user has already joined this game and has an open session, let's not make them pay twice, shall we?
			$content = "<p>You have already paid the entry fee of <b>" . $puzzle->fees->entry . $CURRENCY_IMG . "</b> and you already have an open session in this game.  <br>Would you like to rejoin it?  This action will not cost you anything.</p>";
		}else{
			//they haven't paid, warn them about the consequences
			$content = "<p>To attempt this puzzle will cost you an entry fee of <b>" . $puzzle->fees->entry . $CURRENCY_IMG . "</b> (you currently have <b>" . $user->wallet->available . $CURRENCY_IMG . "</b> available)<br>Are you sure you want to pay this?</p>";
		}
		
		$content .=<<<EOT
<p>
  <a href="index.php" class="btn btn-danger btn-lg">No</a>
  <a href="game.php?id={$_REQUEST['id']}" class="btn btn-success btn-lg">Yes</a>
</p>				
EOT;
		break;
	case "games":
		//shows listing of all games
		$client = new couchClient ($DB_ROOT,"puzzles");
		try{
			$results = $client->getView('listing','allactive');
		}
		catch (Exception $c){
			//map wasn't found
			handleError("noview");
		}
		//TODO: give listing of games you are currently playing
		$body = str_replace("###HEADING###", ((count($results) == 1)?"There is currently 1 game to join":"There are currently " . count($results) . " games to join"), $body);
		$content = '<div class="list-group">';
		$i = 0;
		while ($i < count($results->rows)){
			$content .= "<a href='index.php?type=play&id=" . $results->rows[$i]->id . "' class='list-group-item'><span class='badge'>Entry: " . $results->rows[$i]->value[5]->entry . "$CURRENCY_IMG - Reward: " . $results->rows[$i]->value[5]->reward . "$CURRENCY_IMG</span>" . $results->rows[$i]->value[1] . " by " . $results->rows[$i]->value[0] . "<br>Dimensions: " . $results->rows[$i]->value[3]->width . "x" . $results->rows[$i]->value[3]->height . "<br>" . getDifficulty($results->rows[$i]->value[3], $results->rows[$i]->value[4]) . "</a>";
			$i++;
		}
		$content .= "</div>";
		break;
	case "about":
		//about this game and stuff
		$body = str_replace("###HEADING###", "About SoMaze", $body);
		$about = file_get_contents('templates/about.inc');
		$content = "<p>$about</p>";
		break;
	case "contact":
		//contact me...or don't
		$body = str_replace("###HEADING###", "Contact the creator", $body);
		$content = "<p>The creator can be contacted via this <a href='http://evilmousestudios.com/contactme.html'>form</a></p>";
	case "account":
		if (!isset($_SESSION['user'])){
			//they aren't logged in, so why are they accessing the account page?
			handleError("notloggedin");
		}
		$body = str_replace("###HEADING###", "Account settings", $body);
		if (isset($_REQUEST["action"])){
			$action = $_REQUEST["action"];
		}else{
			$action = "none";
		}
		switch ($action){
			case "nickname":
				//for changing your nickname
				if (!isset($_REQUEST["nickname"])){
					handleError("nonick");
				}
				$nickname = changeNickname($_REQUEST["nickname"]);
				$content = "<p>Your nickname has been changed to <b>" . $nickname . "</b></p>";
				break;
			default:
				//just display the menu
				$content = "<p>Your nickname is currently <b>" . $_SESSION['nickname'] . "</b> <i>(which we all love)</i><br>To change it, type in your desired nickname in the box below and click submit.</p>";
				$content .=<<<EOT
	<form role="form" action="index.php" method="post">
	<div class="form-group">
    	<label for="nickname">Nickname</label>
		<input type="text" class="form-control" name="nickname" placeholder="{$_SESSION['nickname']}">
		<input type="hidden" name="type" value="account">
		<input type="hidden" name="action" value="nickname">
	</div>
		<button type="submit" class="btn btn-primary">Change Nickname</button>
	</form>			
EOT;
				break;
		}
	
		break;
	default:
		//default landing page
		$body = str_replace("###HEADING###", "SoMaze - The crypto maze game", $body);
		$content = "<p>Much traps. Many deaths. Such coin. So maze. Wow.</p>";
		break;	
}

//adds account specific html to the body
$body = formatLogin($body);
//put the content in
$body = str_replace("###CONTENT###", $content, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;

//functions start here!
function changeNickname($nickname){
	$user = getDoc($_SESSION['user'], "users");
	$user->nickname = $nickname;
	$response = setDoc($user, "users");
	$_SESSION['nickname'] = $nickname;
	return $nickname;
}
?>