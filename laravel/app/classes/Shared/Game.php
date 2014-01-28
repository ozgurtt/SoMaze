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
				$returnObj->status = $game->status;
				if ($returnObj->hp <= 0){
					//user is either dead or has won, handle bot
					if ($returnObj->tileType == 2){
						//win conditions
						$creator = false;
						if (\Session::has('creator')){
							if (\Session::get('creator') == $puzzle->_id){
								$creator = true;
							}
						}
						if ($creator == true){
							//this is the creator who just beat the game
							\Session::forget('creator');
							$puzzle->active = true;
							$response = \CouchDB::setDoc($puzzle, "puzzles");
							$user = \CouchDB::getDoc(\Session::get('user'), "users");
							$returnObj->alert = Game::buildAlert("success", "You've solved the puzzle, and it's now activated!", false);
						}else{
							//if we're paying out money, we need to be SURE, this puzzle is open
							$puzzle = \CouchDB::getDoc($puzzle->_id, "puzzles");
							if ($puzzle->stats->solved == false){
								//if the puzzle hasn't been solved by the time you're solving it, yay!
								\Coins\Dogecoin::rewardUser($puzzle->creator->id, \Session::get('user'), $puzzle->fees->reward, 0);
								$user = \CouchDB::getDoc(\Session::get('user'), "users");
								$user->stats->wins++;
								//we set solved to be true, but not active to false, this should trigger the puzzle write
								$puzzle->stats->solved = true;
								$puzzle->stats->winner = $user->_id;
								$puzzle->stats->windate = time();
								$puzzle->stats->winnick = \Session::get('nickname');
								$puzzle->stats->winstatus = \Session::get('status');
							}else{
								//the puzzle has already been solved, if only you were a little bit faster
								$user = \CouchDB::getDoc(\Session::get('user'), "users");
								$returnObj->alert = Game::buildAlert("danger", "Someone solved the puzzle before you!", false);
							}
						}
					}else{
						//they lost :(
						$user = \CouchDB::getDoc(\Session::get('user'), "users");
						$user->stats->losses++;
					}
					unset($user->games->solver->{$puzzle->_id});
					//remove the reference from the user doc
					$response = \CouchDB::setDoc($user, "users");
					//delete the game
					$response = \CouchDB::deleteDoc($game, "games");
					//delete the puzzle if we didn't JUST set it to active
					if ($puzzle->active == true && $puzzle->stats->solved == true){
						$puzzle->active = false;
						$response = \CouchDB::setDoc($puzzle, "puzzles");
						error_log("convertmove:  set puzzle to false and saved");
						//$response = \CouchDB::deleteDoc($puzzle, "puzzles");
					}
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
		if (!in_array($tileID, $player->movechain) || $tiles->tiles[$tile]->effect->rearm == true){
			//single damage.  if the user hits it twice, the second time does no damage
			$player->hp += $tiles->tiles[$tile]->effect->hp;
			$player = Game::applyStatus($player, $tile, $tileID);
			//!in_array prevent stacking of statuses...unless that's what we want?
			if ($tiles->tiles[$tile]->effect->status != "none" && !in_array($tiles->tiles[$tile]->effect->status, $player->status)){
				array_push($player->status, $tiles->tiles[$tile]->effect->status);
			}
		}
		return $player;
	}
	
	public static function applyStatus($player, $tile, $tileID){
		$tiles = \CouchDB::getDoc("tiles", "misc");
		//check for removing statuses
		foreach ($player->status as $k => $status){
			//if $status has an remove condition, let's remove it
			if ($tiles->statuses->{$status}->remove == $tiles->tiles[$tile]->effect->status){
				//remove condition matches, remove the status
				unset($player->status[$k]);
				$player->status = array_values($player->status);
			}else{
				if (in_array($status, $player->status)){
					//apply the status effect
					$player->hp += $tiles->statuses->{$status}->effect;
					error_log("applying the tile effect of status: " . $status . " hp" . $player->hp);
				}
			}
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
	
	public static function buildAlert($type, $text, $dismissable){
		//this function builds an object for a client side alert
		$alert = new \stdClass();
		$alert->type = $type;
		$alert->text = $text;
		$alert->dismissable = $dismissable;
		return $alert;
	}

}