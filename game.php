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





//pre body and send it out
$body = str_replace("###CONTENT###", $content, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;

//functions start here!

function handleError($error){
	switch($error){
		case "noid":
			$content = "<p>You didn't provide a game ID!</p>";
			break;
	}
	print $content;
	die();
}
?>