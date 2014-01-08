<?php
//common vars and such
require_once "lib/common.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Chicago');

//set vars / error trap
if (!isset($_SESSION['user'])){
	//they aren't logged in, so they can't play until they do
	handleError("notloggedin");
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

switch ($command){
		case "getTiles":
			//used to get tiles
			$content = json_encode(getDoc("tiles", "misc"));
			break;
		case "getMap":
			//used to get blank map
			$content = json_encode(createPuzzle($_REQUEST['width'], $_REQUEST['height']));
			break;
		default:
			if ($_REQUEST['width'] < $MIN_PUZZLE_SIZE || $_REQUEST['width'] > $MAX_PUZZLE_SIZE){
				handleError("badparams");
			}
			if ($_REQUEST['height'] < $MIN_PUZZLE_SIZE || $_REQUEST['height'] > $MAX_PUZZLE_SIZE){
				handleError("badparams");
			}
			$body = str_replace("###HEADING###", "Puzzle creation", $body);
			$content = "To create a puzzle, click on the tile you want in the library, and after you do, click on all the tiles you want to look like that on your puzzle.  When you are done, click finish to move to the next step.";
			$divcontent = <<<EOT
<div id="game">
</div>
<div id="alerts">
</div>
<div id="tiles">
<div id="tileinfo">
Select a tile
</div>
</div>
EOT;
			
			
			$body = str_replace("###DIV###", $divcontent, $body);
			$body = str_replace("###JS###", $JS_CREATE_SOURCE, $body);
			$body = str_replace("###SNIPPET###", makeJS(array("WIDTH"=>$_REQUEST['width'], "HEIGHT"=>$_REQUEST['height'])), $body);

			break;
		
}

//pre body and send it out
if ($api == true){
	//if we're using the api, just return what they want
	print $content;
}else{
	//if we're not, make it pretttty
	//adds account specific html to the body
	$body = formatLogin($body);
	//format the rest
	$body = str_replace("###CONTENT###", $content, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
}
die();

//functions start here!
function createPuzzle($width, $height){
	$returnObj = new stdClass();
	$returnObj->active = false;
	$returnObj->dimensions = new stdClass();
	$returnObj->dimensions->width = intval($width);
	$returnObj->dimensions->height = intval($height);
	$returnObj->map = array_fill(0, (($width * $height)), 0);
	return $returnObj;
}
?>