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

switch ($command){
	case "getTiles":
		//used to get tiles
		$content = json_encode(getDoc("tiles", "misc"));
		break;
	case "getMap":
		$puzzle = getDoc($_REQUEST["id"], "puzzles");
		$_SESSION['puzzle'] = clone $puzzle;
		//load the user
		$user = getDoc($_SESSION["user"], "users");
		//if they have a game session for the map (which they should)
		if ($_SESSION['game'] == $user->games->solver->{$_REQUEST['id']}){
			//load the game
			$game = getDoc($_SESSION['game'], "games");
			//if they've moved at all
			if (count($game->movechain) != 0){
				//set their starting place to the last place they were
				$puzzle->map = str_replace(1, 0, $puzzle->map);
				$puzzle->map[end($game->movechain)] = 1;
			}
		}else{
			handleError("notingame");
		}
		//loaded the map, now store it in the session and then convert it
		$content = json_encode(convertMap($puzzle));
		break;
	case "move":
		if (!isset($_REQUEST['tileID'])){handleError("notile-move");}
		if (!isset($_REQUEST['sessionID'])){handleError("nosession");}
		//if the game var isn't set in the session
		if (!isset($_SESSION['game'])){
			//it means that the user hasn't been loaded ever
			error_log("game session doesn't exist, get it from the user");
			$user = getDoc($_SESSION["user"], "users");
			if (!isset($user->games->solver->{$_REQUEST['id']})){
				//user isn't a solver in this game
				handleError("notingame");
			}
			$_SESSION['game'] = $user->games->solver->{$_REQUEST['id']};
		}
		//user is a solver, so let's load the game
		$game = getDoc($_SESSION['game'], "games");
		if ($game->gameid != $_REQUEST['id']){
			//session game id is stale, update it
			$user = getDoc($_SESSION["user"], "users");
			if (!isset($user->games->solver->{$_REQUEST['id']})){
				//user isn't a solver in this game
				handleError("notingame");
			}	
			//set the game id into the session var
			$_SESSION['game'] = $user->games->solver->{$_REQUEST['id']};
			//get the new game that was just got from the user
			$game = getDoc($_SESSION['game'], "games");
		}
		//check to make sure the puzzle isn't stale
		if ($_SESSION['puzzle']->_id != $_REQUEST['id']){
			error_log("puzzle is stale, getting it again");
			//the puzzle is stale, let's get a new copy of it
			$puzzle = getDoc($_REQUEST["id"], "puzzles");
			$_SESSION['puzzle'] = clone $puzzle;
		}
		if ($game->hp <= 0){handleError("youredead");}
		$content = json_encode(convertMove($game, $_SESSION['puzzle'], $_REQUEST['tileID'], $_REQUEST['sessionID']));
		break;
	default:
		$user = getDoc($_SESSION['user'], "users");
		$puzzle = getDoc($_REQUEST['id'], "puzzles");
		if (isset($user->games->solver->$_REQUEST['id'])){
			//this person has already paid
			$_SESSION['game'] = $user->games->solver->{$_REQUEST['id']};
			$game = getDoc($_SESSION['game'], "games");
			$amount = 0;
			$joinfee = "<p><i>You have rejoined this game for free.</i></p>";
		}else{
			//create the game in their active games and save it
			$game = createGame($puzzle->start);
			$user->games->solver->$_REQUEST['id'] = $game->_id;
			$response = setDoc($user, "users");
			//then set the session
			$_SESSION['game'] = $user->games->solver->$_REQUEST['id'];
			//change them for entry
			$amount = payUser($_SESSION['user'], $puzzle->creator, $puzzle->fees->entry, $puzzle->fees->creation);
			$joinfee = "<p><i>You just paid <b>" . $amount . $CURRENCY_IMG . "</b> to join this game (you have <b>" . $user->wallet->available . $CURRENCY_IMG . "</b> left)</i></p>";
		}
		$body = str_replace("###HEADING###", $puzzle->title . " by " . $puzzle->nickname, $body);
		$content = $joinfee . "<p>" . $puzzle->desc . "</p>";
		//do tile difficulty math
		$diffSpan = getDifficulty($puzzle->dimensions, $puzzle->traps);
		$divcontent = <<<EOT
<div id="game">
</div>
<div id="alerts">
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Puzzle Statistics</h3>
  </div>
  <div class="panel-body">
    <p>$diffSpan</p>
    <table id='fee'>
    <tr><td class='fee'>Creation Fee</td><td><b>{$puzzle->fees->creation}</b>$CURRENCY_IMG</td></tr>
    <tr><td class='fee'>Entry Fee</td><td><b>{$puzzle->fees->entry}</b>$CURRENCY_IMG</td></tr>
    <tr><td class='fee'>Reward Fee</td><td><b>{$puzzle->fees->reward}</b>$CURRENCY_IMG</td></tr>
    </table>
  </div>
</div>
EOT;
		$body = str_replace("###DIV###", $divcontent, $body);
		$body = str_replace("###SNIPPET###", makeJS(array("GAME_ID"=>$_REQUEST['id'], "sessionID"=>$game->sessionID)), $body);
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
	//adds account specific html to the body
	$body = formatLogin($body);
	//format the rest
	$body = str_replace("###CONTENT###", $content, $body);
	$body = str_replace("###JS###", $JS_GAME_SOURCE, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
}
die();

//functions start here!

function makeJS($vars){
	//makes a JS snippet based on the args array you give it
	$snippet = "<script>\n\t";
	//var GAME_ID = '" . $_REQUEST['id'] . "';\n\tvar SESSION_ID = '" . "###SESSION###" . "'\n</script";
	foreach ($vars as $k => $var){
		$snippet .= "var " . $k . " ='" . $var . "';\n\t";
	}
	$snippet .= "</script>";
	return $snippet;
}

function convertMap($puzzle){
	//when given a map array, converts it for the client (removes all tiles they shouldn't see
	$tiles = getDoc("tiles", "misc");
	$hiddenTiles = array();
	foreach ($tiles->tiles as $i => $tile){
		if ($tile->hidden == true){
			array_push($hiddenTiles, $i);
		}
	}
	foreach ($hiddenTiles as $tile){
		//walks through each hidden tile
		$puzzle->map = str_replace($tile, 0, $puzzle->map);
	}
	return $puzzle;
}

function convertMove($game, $puzzle, $tileID, $sessionID){
	global $DB_ROOT;
	//given the puzzle, the player, and the proposed move, sends information back to the client
	//get json from client, {tileID, sessionID} ?player id?
	//send json back, {accepted, tileID, tileType, hp, sessionID}
	//error_log("debug: " . json_encode($puzzle->players->{'$player'}));
	$returnObj = new stdClass();
	//first check if the request ID is valid
	if ($sessionID == $game->sessionID){
		//the request matches what we are expecting, let's check if it's a valid move
		if (checkIfNeighbor($puzzle, end($game->movechain), $tileID) == true){
			//the move is valid, let's do calculations on damage
			$returnObj->accepted = true;
			$returnObj->tileID = $tileID;
			$returnObj->tileType = $puzzle->map[$tileID];
			$returnObj->sessionID = generateSession();
			$game->sessionID = $returnObj->sessionID;
			$game = applyEffects($game, $puzzle->map[$tileID], $tileID);
			array_push($game->movechain, intval($tileID));
			$returnObj->hp = $game->hp;
			if ($returnObj->hp <= 0){
				//user is either dead or has won, handle bot
				if ($returnObj->tileType == 2){
					//win conditions
					rewardUser($puzzle->creator, $_SESSION['user'], $puzzle->fees->reward, 0);
					$user = getDoc($_SESSION['user'], "users");
					$user->stats->wins++;
				}else{
					$user = getDoc($_SESSION['user'], "users");
					$user->stats->losses++;
				}
				unset($user->games->solver->{$puzzle->_id});
				//remove the reference from the user doc
				$response = setDoc($user, "users");
				//delete the game
				$response = deleteDoc($game, "games");
				//set the puzzle to active=false
			}else{
				//write player position to database
				$response = setDoc($game, "games");	
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

function applyEffects($player, $tile, $tileID){
	$tiles = getDoc("tiles", "misc");
	if (!in_array($tileID, $player->movechain)){
		//single damage.  if the user hits it twice, the second time does no damage
		$player->hp += $tiles->tiles[$tile]->effect->hp;
	}elseif ($tiles->tiles[$tile]->effect->rearm == true){
		$player->hp += $tiles->tiles[$tile]->effect->hp;
	}
	return $player;
}

function checkIfNeighbor($puzzle, $start, $finish){
	//checks if $finish is a neighbor to $start
	if ($finish < 0 || $finish > ($puzzle->dimensions->height * $puzzle->dimensions->width)){return false;}
	if ($start + 1 == $finish && ($start % $puzzle->dimensions->width) != ($puzzle->dimensions->width - 1)){
		//do left movement
		return true;
	}elseif ($start - 1 == $finish && ($start % $puzzle->dimensions->width) != 0){
		//do right movement
		return true;
	}elseif (abs($start - $finish) == $puzzle->dimensions->width){
		//this is a top or bottom match
		return true;
	}
	return false;
}

function payUser($from, $to, $amount, $fee){
	//used to pay users (for entry fees)
	$fromuser = getDoc($from, "users");
	if ($fromuser->wallet->available < $amount){
		//they don't have the funds
		handleError("nofunds");
	}
	$touser = getDoc($to, "users");
	//pay the user the entrance fee
	$touser->wallet->available += $amount;
	//take the creation fee (we should probably log this)
	$touser->wallet->available -= $fee;
	//take the entrance fee from the user
	$fromuser->wallet->available -= $amount;
	if ($fromuser->wallet->available >= 0){
		$response = setDoc($fromuser, "users");
		$response = setDoc($touser, "users");
		return $amount;
	}else{
		//they don't have the funds
		//i know i literally JUST checked this, but with money, you can't be too safe, can you?
		handleError("nofunds");
	}
	//something went wrong, but i have no idea what that might be.
	return 0;
}

function rewardUser($from, $to, $amount, $fee){
	//used to reward users (for winning)
	$fromuser = getDoc($from, "users");
	if ($fromuser->wallet->locked < $amount){
		//they don't have the funds
		handleError("nofunds");
	}
	$touser = getDoc($to, "users");
	//pay the user the entrance fee
	$touser->wallet->available += $amount;
	//take the creation fee (we should probably log this)
	$touser->wallet->available -= $fee;
	//take the entrance fee from the user
	$fromuser->wallet->locked -= $amount;
	if ($fromuser->wallet->locked >= 0){
		$response = setDoc($fromuser, "users");
		$response = setDoc($touser, "users");
		return $amount;
	}else{
		//they don't have the funds
		//i know i literally JUST checked this, but with money, you can't be too safe, can you?
		handleError("nofunds");
	}
	//something went wrong, but i have no idea what that might be.
	return 0;
}

function createGame($start){
	//creates a new blank game and returns it
	$game = new stdClass();
	$game->_id = $_REQUEST['id'] . " - " . $_SESSION['user'];
	$game->gameid = $_REQUEST['id'];
	$game->userid = $_SESSION['user'];
	$game->hp = 100;
	$game->started = time();
	$game->sessionID = generateSession();
	$game->movechain = array();
	array_push($game->movechain, $start);
	$response = setDoc($game, "games");
	return $game;
}
?>