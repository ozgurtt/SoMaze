<?php namespace Shared;

class Game {

    public static function getDifficulty($dimArr, $trapArr){
		//give it the traps array and the dimensions array, and it returns the correct HTMl code for that difficulty
		$tiles = intval($dimArr->height) * intval($dimArr->width);
		$traps = 0;
		foreach ($trapArr as $trap){
			$traps += intval($trap);
		}
		$difficulty = round(($traps / $tiles)*100, 2);
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
		return array('difficulty' => $difficulty, 'label' => $label, 'note' => $note);
	}
	
	public static function payUser($from, $to, $amount, $fee){
		//used to pay users (for entry fees)
		$fromuser = \CouchDB::getDoc($from, "users");
		if ($fromuser->wallet->available < $amount){
			//they don't have the funds
			return \Shared\Error::handleError("nofunds");
		}
		$touser = \CouchDB::getDoc($to, "users");
		//pay the user the entrance fee
		$touser->wallet->available += $amount;
		//take the creation fee (we should probably log this)
		$touser->wallet->available -= $fee;
		//take the entrance fee from the user
		$fromuser->wallet->available -= $amount;
		if ($fromuser->wallet->available >= 0){
			$response = \CouchDB::setDoc($fromuser, "users");
			$response = \CouchDB::setDoc($touser, "users");
			return $amount;
		}else{
			//they don't have the funds
			//i know i literally JUST checked this, but with money, you can't be too safe, can you?
			return \Shared\Error::handleError("nofunds");
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
	}

	public static function rewardUser($from, $to, $amount, $fee){
		//used to reward users (for winning)
		$fromuser = \CouchDB::getDoc($from, "users");
		if ($fromuser->wallet->locked < $amount){
			//they don't have the funds
			return \Shared\Error::handleError("nofunds");
		}
		$touser = \CouchDB::getDoc($to, "users");
		//pay the user the entrance fee
		$touser->wallet->available += $amount;
		//take the creation fee (we should probably log this)
		$touser = Game::payMe($touser, $fee);
		//take the entrance fee from the user
		$fromuser->wallet->locked -= $amount;
		if ($fromuser->wallet->locked >= 0){
			$response = \CouchDB::setDoc($fromuser, "users");
			$response = \CouchDB::setDoc($touser, "users");
			return $amount;
		}else{
			//they don't have the funds
			//i know i literally JUST checked this, but with money, you can't be too safe, can you?
			\Shared\Error::handleError("nofunds");
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
	}
	
	public static function lockFunds($from, $amount){
		//locks funds to prepare for reward
		$fromuser = \CouchDB::getDoc($from, "users");
		if ($fromuser->wallet->available < $amount){
			//you broke dawg
			return \Shared\Error::handleError("nofunds");
		}else{
			$fromuser->wallet->available -= $amount;
			$fromuser->wallet->locked += $amount;
			$response = \CouchDB::setDoc($fromuser, "users");
			return $amount;
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
	}
	
	public static function payMe($from, $amount){
		//pay ME billy
		$from->wallet->available -= $amount;
		return $from;
	}
	
	public static function payCreationFee($from, $amount){
		//to pay and save
		$fromuser = \CouchDB::getDoc($from, "users");
		if ($fromuser->wallet->available < $amount){
			//you broke dawg
			return \Shared\Error::handleError("nofunds");
		}else{
			$fromuser = Game::payMe($fromuser, $amount);
			$response = \CouchDB::setDoc($fromuser, "users");
			return $amount;
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
		
		
	}
	
	public static function convertMap($puzzle){
		//when given a map array, converts it for the client (removes all tiles they shouldn't see
		$tiles = \CouchDB::getDoc("tiles", "misc");
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
	
	public static function convertMove($game, $puzzle, $tileID, $sessionID){
		//given the puzzle, the player, and the proposed move, sends information back to the client
		//get json from client, {tileID, sessionID} ?player id?
		//send json back, {accepted, tileID, tileType, hp, sessionID}
		//error_log("debug: " . json_encode($puzzle->players->{'$player'}));
		$returnObj = new \stdClass();
		//first check if the request ID is valid
		if ($sessionID == $game->sessionID){
			//the request matches what we are expecting, let's check if it's a valid move
			if (Game::checkIfNeighbor($puzzle, end($game->movechain), $tileID) == true){
				//the move is valid, let's do calculations on damage
				$returnObj->accepted = true;
				$returnObj->tileID = $tileID;
				$returnObj->tileType = $puzzle->map[$tileID];
				$returnObj->sessionID = Common::generateSession();
				$game->sessionID = $returnObj->sessionID;
				$game = Game::applyEffects($game, $puzzle->map[$tileID], $tileID);
				array_push($game->movechain, intval($tileID));
				$returnObj->hp = $game->hp;
				if ($returnObj->hp <= 0){
					//user is either dead or has won, handle bot
					if ($returnObj->tileType == 2){
						//win conditions
						rewardUser($puzzle->creator, \Session::get('user'), $puzzle->fees->reward, 0);
						$user = \CouchDB::getDoc(\Session::get('user'), "users");
						$user->stats->wins++;
					}else{
						$user = \CouchDB::getDoc(\Session::get('user'), "users");
						$user->stats->losses++;
					}
					unset($user->games->solver->{$puzzle->_id});
					//remove the reference from the user doc
					$response = \CouchDB::setDoc($user, "users");
					//delete the game
					$response = \CouchDB::deleteDoc($game, "games");
					//set the puzzle to active=false
				}else{
					//write player position to database
					$response = \CouchDB::setDoc($game, "games");	
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
	
	public static function applyEffects($player, $tile, $tileID){
		$tiles = \CouchDB::getDoc("tiles", "misc");
		if (!in_array($tileID, $player->movechain)){
			//single damage.  if the user hits it twice, the second time does no damage
			$player->hp += $tiles->tiles[$tile]->effect->hp;
		}elseif ($tiles->tiles[$tile]->effect->rearm == true){
			$player->hp += $tiles->tiles[$tile]->effect->hp;
		}
		return $player;
	}
	
	public static function checkIfNeighbor($puzzle, $start, $finish){
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
	
	public static function scoreMap($puzzle){
		$COMMON = \Config::get('common');
		//scores a puzzle
		$tiles = \CouchDB::getDoc("tiles", "misc");
		$fee = 0;
		foreach($puzzle['map'] as $tile){
			$fee += $tiles->tiles[$tile]->cost->{$COMMON['CURRENCY']};
		}
		return $fee;
	}
	
	public static function isValid($puzzle){
		//checks to make sure the puzzle has exactly 1 entrance and exit
		$values = array_count_values($puzzle['map']);
		if (!isset($values[1])){return false;}
		if (!isset($values[2])){return false;}
		if ($values[1] == 1 && $values[2] == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public static function populateTraps($puzzle){
		//give us the puzzle, and we'll return a traps object
		$tiles = \CouchDB::getDoc("tiles", "misc");
		$values = array_count_values($puzzle['map']);
		$traps = new \stdClass();
		$i=3;
		while ($i < count($tiles->tiles)){
			if (isset($values[$i])){
				//this tile exists
				$traps->{strval($i)} = $values[$i];
			}
			$i++;
		}
		return $traps;
		
	}

}