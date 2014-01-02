<?
date_default_timezone_set('America/Chicago');

$TITLE = "SoMaze";
$VERSION = ".01";

//html snippets
$JS_SOURCE = '<script src="js/game.js"></script>';

//prep body
$body = file_get_contents('templates/body.inc');
$body = str_replace("###TITLE###", $TITLE, $body);

//set vars
if (!isset($_REQUEST["id"])){
	//they didn't provide an ID for the game they are trying to access
	handleError("noid");
}else{
	$gameID = $_REQUEST["id"];
}


$body = str_replace("###HEADING###", "SoMaze Game ID: " . $gameID, $body);

//load the game

//check to see if this game's relation to the user calling this

//if they are the player, find what state the game is in, and start it

//if they are the creator, show them stats and let them manage it



//pre body and send it out
$body = str_replace("###CONTENT###", $content, $body);
$body = str_replace("###JS###", $JS_SOURCE, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;

//functions start here!

function handleError($error){
	global $body;
	$return = "<br>Click <a href='index.php'>here</a> to go home";
	switch($error){
		case "noid":
			$error = "No Game ID";
			$content = "<p>You didn't provide a game ID!</p>";
			break;
	}
	$body = str_replace("###HEADING###", "Error: " . $error, $body);
	$body = str_replace("###CONTENT###", $content . $return, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
	die();
}
?>