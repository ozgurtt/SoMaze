<?
date_default_timezone_set('America/Chicago');


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
$TITLE = "SoMaze";
$VERSION = ".01";

//html snippets
$JS_SOURCE = '<script src="js/###JS###"></script>';


//prep body
$body = file_get_contents('templates/body.inc');
$body = str_replace("###TITLE###", $TITLE, $body);


switch($type){
	case "games":
		//shows listing of all games
		$body = str_replace("###HEADING###", "There are currently 0 games to join", $body);
		$content = "<p>Test Game 0: <a href='game.php?id=1'>Join</a></p>";
		break;
	case "about":
		//about this game and stuff
		$body = str_replace("###HEADING###", "About SoMaze", $body);
		$content = "<p>SoMaze is a game created by <a href='http://evilmousestudios.com'>Evil Mouse Studios</a> for use with various cryptocurrencies.  There will be more information here once it's available.</p>";
		break;
	case "contact":
		//contact me...or don't
		$body = str_replace("###HEADING###", "Contact the creator", $body);
		$content = "<p>The creator can be contacted via this <a href='http://evilmousestudios.com/contactme.html'>form</a></p>";
		break;
	default:
		//default landing page
		$body = str_replace("###HEADING###", "SoMaze - The crypto maze game", $body);
		$content = "<p>Much traps. Many deaths. Such coin. So maze. Wow.</p>";
		break;	
}
$body = str_replace("###CONTENT###", $content, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;

//functions start here!
?>