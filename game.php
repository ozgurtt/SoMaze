<?

require_once "lib/couch.php";
require_once "lib/couchClient.php";
require_once "lib/couchDocument.php";

date_default_timezone_set('America/Chicago');

$TITLE = "SoMaze";
$VERSION = ".01";

//html snippets
$JS_SOURCE = '<script src="js/game.js"></script>';

$DB_ROOT = "http://127.0.0.1:5984";

//prep body
$body = file_get_contents('templates/body.inc');
$body = str_replace("###TITLE###", $TITLE, $body);

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
		$tiles = intval($puzzle->dimensions->height) * intval($puzzle->dimensions->width);
		$traps = 0;
		foreach ($puzzle->traps as $trap){
			$traps += intval($trap);
		}
		$difficulty = ($traps / $tiles)*100;
		if ($difficulty < 20){
			//easy
			$label = "label-success";
			$note = "Easy";
		}else if ($difficulty < 40){
			//medium
			$label = "label-warning";
			$note = "Medium";
		}else{
			//hard!
			$label = "label-danger";
			$note = "Hard";
		}
		$stats = "THIS IS A TEST";
		$divcontent = <<<EOT
<div id="game">
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Puzzle Statistics</h3>
  </div>
  <div class="panel-body">
    <p>Difficulty: $difficulty% <span class="label $label">$note</span></p>
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
	$body = str_replace("###JS###", $JS_SOURCE, $body);
	$body = str_replace("###SNIPPET###", $JS_ID_SNIPPET, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
}
die();

//functions start here!

function handleError($error, $meta=null){
	global $body;
	$return = "<br>Click <a href='index.php'>here</a> to go home";
	switch($error){
		case "noid":
			$error = "No Game ID";
			$content = "<p>You didn't provide a game ID!</p>";
			break;
		case "noid":
			$error = "No API Command";
			$content = "<p>To use the API, you must provide a command!</p>";
			break;
		case "nodoc":
			$error = "Document Not Found";
			$content = "<p>No document found with ID: " . $meta . "</p>";
			break;
	}
	$body = str_replace("###HEADING###", "Error: " . $error, $body);
	$body = str_replace("###CONTENT###", $content . $return, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
	die();
}

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