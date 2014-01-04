<?

require_once "lib/couch.php";
require_once "lib/couchClient.php";
require_once "lib/couchDocument.php";
//common vars and such
require_once "lib/common.php";

date_default_timezone_set('America/Chicago');

//set vars / error trap
if (!isset($_REQUEST["id"])){
	//they didn't provide an ID for the game they are trying to access
	handleError("noid");
}else{
	$gameID = $_REQUEST["id"];
}

if (isset($_REQUEST["api"])){
	//they are trying to use the API to perform tasks. DON'T serve them a webpage
	if (!isset($_REQUEST["command"])){
		//they didn't include a command with the API
		handleError("nocommand");
	}
	$api = true;
	$command = $_REQUEST["command"];
}else{
	$api = false;
	$command = "none";
}

$JS_ID_SNIPPET = "<script>var GAME_ID = '" . $_REQUEST['id'] . "';</script";

switch ($command){
	case "getMap":
		$client = new couchClient ($DB_ROOT,"puzzles");
		try{
			$puzzle = $client->getDoc($_REQUEST["id"]);
		}
		catch (Exception $c){
			//map wasn't found
			handleError("nodoc", $_REQUEST["id"]);
		}
		//loaded the map, now convert it
		$content = json_encode(convertMap($puzzle));
		break;
	default:
		//serve the game on the main webpage
		$client = new couchClient ($DB_ROOT,"puzzles");
		try{
			$puzzle = $client->getDoc($_REQUEST["id"]);
		}
		catch (Exception $c){
			//map wasn't found
			handleError("nodoc", $_REQUEST["id"]);
		}
		$body = str_replace("###HEADING###", $puzzle->title . " by " . $puzzle->creator, $body);
		$content = "<p>" . $puzzle->desc . "</p>";
		//do tile difficulty math
		$diffSpan = getDifficulty($puzzle->dimensions, $puzzle->traps);
		$divcontent = <<<EOT
<div id="game">
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Puzzle Statistics</h3>
  </div>
  <div class="panel-body">
    <p>$diffSpan</p>
    <table id='fee'>
    <tr><td class='fee'>Creation Fee</td><td>{$puzzle->fees->creation}</td></tr>
    <tr><td class='fee'>Entry Fee</td><td>{$puzzle->fees->entry}</td></tr>
    <tr><td class='fee'>Reward Fee</td><td>{$puzzle->fees->reward}</td></tr>
    </table>
  </div>
</div>
EOT;
		$body = str_replace("###DIV###", $divcontent, $body);
		break;
		
}

//load the game
//check to see if this game's relation to the user calling this

//if they are the player, find what state the game is in, and start it

//if they are the creator, show them stats and let them manage it



//pre body and send it out
if ($api == true){
	//if we're using the api, just return what they want
	print $content;
}else{
	//if we're not, make it pretttty
	$body = str_replace("###CONTENT###", $content, $body);
	$body = str_replace("###JS###", $JS_GAME_SOURCE, $body);
	$body = str_replace("###SNIPPET###", $JS_ID_SNIPPET, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
}
die();

//functions start here!

function convertMap($puzzle){
	//when given a map array, converts it for the client (removes all tiles they shouldn't see
	$hiddenTiles = array(4);
	foreach ($hiddenTiles as $tile){
		//walks through each hidden tile
		$puzzle->map = str_replace($tile, 0, $puzzle->map);
	}
	return $puzzle;
	
}
?>