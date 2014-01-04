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

$JS_ID_SNIPPET = "<script>\n\tvar GAME_ID = '" . $_REQUEST['id'] . "';\n\tvar SESSION_ID = '" . generateSession() . "'\n</script";

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
	case "move":
		$client = new couchClient ($DB_ROOT,"puzzles");
		try{
			$puzzle = $client->getDoc($_REQUEST["id"]);
		}
		catch (Exception $c){
			//map wasn't found
			handleError("nodoc", $_REQUEST["id"]);
		}
		//loaded the map, and player, now do the move stuff
		if (!isset($_REQUEST['tileID'])){handleError("notile-move");}
		if (!isset($_REQUEST['sessionID'])){handleError("nosession");}
		$content = json_encode(convertMove("SLoW", $puzzle, $_REQUEST['tileID'], $_REQUEST['sessionID']));
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
    <tr><td class='fee'>Creation Fee</td><td>{$puzzle->fees->creation}$CURRENCY_IMG</td></tr>
    <tr><td class='fee'>Entry Fee</td><td>{$puzzle->fees->entry}$CURRENCY_IMG</td></tr>
    <tr><td class='fee'>Reward Fee</td><td>{$puzzle->fees->reward}$CURRENCY_IMG</td></tr>
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

function convertMove($player, $puzzle, $tileID, $sessionID){
	global $DB_ROOT;
	//given the puzzle, the player, and the proposed move, sends information back to the client
	//get json from client, {tileID, sessionID} ?player id?
	//send json back, {accepted, tileID, tileType, hp, sessionID}
	//error_log("debug: " . json_encode($puzzle->players->{'$player'}));
	$returnObj = new stdClass();
	//first check if the request ID is valid
	if ($sessionID == $puzzle->players->{$player}->sessionID){
		//the request matches what we are expecting, let's check if it's a valid move next
		if (checkIfNeighbor($puzzle, end($puzzle->players->{$player}->movechain), $tileID) == true){
			//the move is valid, let's do calculations on damage
			$returnObj->accepted = true;
			$returnObj->tileID = $tileID;
			$returnObj->tileType = $puzzle->map[$tileID];
			//TODO: use better session id!
			$returnObj->sessionID = "X";
			array_push($puzzle->players->{$player}->movechain, intval($tileID));
			$puzzle->players->{$player} = applyEffects($puzzle->players->{$player}, $puzzle->map[$tileID]);
			$returnObj->hp = $puzzle->players->{$player}->hp;
			//write player position to database
			$client = new couchClient ($DB_ROOT,"puzzles");
			try {
				$response = $client->storeDoc($puzzle);
			} catch (Exception $e) {
				handleError("badsave", $puzzle->_id);
			}	
		}else{
			$returnObj->accepted = false;
		}
	}else{
		//bad request, either malformed or late
		$returnObj->accepted = false;
	}
	return $returnObj;
}

function applyEffects($player, $tile){
	switch($tile){
		case 3:
			//lava - instadeath
			$player->hp = 0;
			break;
		case 4:
			//mine
			$player->hp -= 50;
			break;
	}
	return $player;
}

function checkIfNeighbor($puzzle, $start, $finish){
	//checks if $finish is a neighbor to $start
	//bound checking needs to go here
	return true;
}
?>