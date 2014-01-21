<?php

class GameController extends BaseController {

	public function showGameListing(){
		$results = CouchDB::getView("listing", "allactive", "puzzles");
		$data = array('results' => $results);
		return View::make('game.solver-listing', $data);
	}

	public function confirmEntry($id){
		$puzzle = CouchDB::getDoc($id, "puzzles");
		$user = CouchDB::getDoc($value = Session::get('user'), "users");
		if (count(get_object_vars($puzzle)) == 0){
			//this puzzle is gone now, let's delete the references to it
			$game = CouchDB::getDoc($user->games->solver->{$id}, "games");
			unset($user->games->solver->{$id});
			//remove the reference from the user doc
			$response = CouchDB::setDoc($user, "users");
			//delete the game
			$response = CouchDB::deleteDoc($game, "games");
			return Shared\Errors::handleError("gameover");
		}
		if ($user->wallet->available < $puzzle->fees->entry){
			return Shared\Errors::handleError("nofunds");
		}
		Session::put('id', $id);
		$data = array('puzzle' => $puzzle,
					  'user'   => $user);
		return View::make('game.solver-confirm', $data);
	}
	
	public function playResponse(){
		//they confirmed and they are back on this page
		Session::flash('playconfirm', true);
		$data = array('id' => Session::get('id'));
		return Redirect::action('GameController@playGame', $data);
	}
	
	public function createResponse(){
		//they confirmed and they are back on this page
		Session::flash('createconfirm', true);
		return Redirect::action('GameController@savePuzzle');
	}
	
	public function playGame($id){
		$GAME_CONFIG = Config::get('game');
		$user = CouchDB::getDoc(Session::get('user'), "users");
		$puzzle = CouchDB::getDoc($id, "puzzles");
		if (isset($user->games->solver->$id)){
			//this person has already paid
			Session::put('game', $user->games->solver->$id);
			$game = CouchDB::getDoc(Session::get('game'), "games");
			$amount = 0;
		}elseif (in_array($puzzle->_id, $user->games->creator)){
			//this is the creator who's playing and they don't have an active game
			$game = CouchDB::createGame($puzzle->start, $id, Session::get('user'));
			$user->games->solver->$id = $game->_id;
			$response = CouchDB::setDoc($user, "users");
			Session::put('game', $user->games->solver->$id);
			Session::put('creator', $puzzle->_id);
			$amount = 0;
		}else{
			//create the game in their active games and save it
			if ($puzzle->active == false || $puzzle->stats->solved == true){
				return Shared\Errors::handleError("notactive");
			}
			if (count(get_object_vars($user->games->solver)) >= $GAME_CONFIG['MAX_SOLVER_PUZZLES']){
				//they can only have one game open at a time
				return Shared\Errors::handleError("toomanysolvers");
			}
			$game = CouchDB::createGame($puzzle->start, $id, Session::get('user'));
			$user->games->solver->$id = $game->_id;
			$user->stats->attempts++;
			$response = CouchDB::setDoc($user, "users");
			//update puzzle stats
			$puzzle->stats->attempts++;
			$puzzle->stats->last = time();
			$response = CouchDB::setDoc($puzzle, "puzzles");
			$puzzle->_rev = $response->rev;
			//then set the session
			Session::put('game', $user->games->solver->$id);
			//change them for entry
			$amount = Shared\Game::payUser(Session::get('user'), $puzzle->creator->id, $puzzle->fees->entry, $puzzle->fees->creation);
		}
		//do tile difficulty math
		$difficulty = Shared\Game::getDifficulty($puzzle->dimensions, $puzzle->traps);
		$data = array('puzzle' => $puzzle,
					  'user'   => $user,
					  'game'   => $game,
					  'amount' => $amount,
					  'difficulty' => $difficulty);
		return View::make('game.solver-play', $data);
	}
	
	public function createPuzzle(){
		return View::make('game.creator-dimensions');
	}
	
	public function makePuzzle(){
		$GAME = Config::get('game');
		if (Input::get('width') < $GAME['MIN_PUZZLE_SIZE'] || Input::get('width') > $GAME['MAX_PUZZLE_SIZE']){
			return Shared\Errors::handleError("badparams");
		}
		if (Input::get('height') < $GAME['MIN_PUZZLE_SIZE'] || Input::get('height') > $GAME['MAX_PUZZLE_SIZE']){
			return Shared\Errors::handleError("badparams");
		}
		$puzzle = CouchDB::createPuzzle(Input::get('width'), Input::get('height'));
		Session::put('puzzle', $puzzle);
		$data = array('width' => Input::get('width'),
					  'height'   => Input::get('height'));
		return View::make('game.creator-make', $data);
	}

	public function confirmCreate(){
		$GAME = Config::get('game');
		//check for min char count on title and desc
		if (strlen(Input::get('title')) <= 2 || strlen(Input::get('desc')) <= 2){
			return Shared\Errors::handleError("badwords");
		}
		//load the puzzle
		if (!Session::has("puzzle")){
			//there's no puzzle session, probably because they just created it and hit back.  I call this "Caleb Syndrome"
			return Shared\Errors::handleError("cantmakepuzzle");
		}
		$sessionPuzzle = Session::get('puzzle');
		//check to make sure the entry fee is high enough (client side js should prevent this)
		if (intval(Input::get('entry')) < $sessionPuzzle->fees->creation){
			return Shared\Errors::handleError("lowentry");
		}
		//check to make sure the reward is high enough (client side js should prevent this)
		if (intval(Input::get('reward')) < intval(Input::get('entry'))){
			return Shared\Errors::handleError("lowreward");
		}
		$user = CouchDB::getDoc(Session::get('user'), "users");
		//check to make sure they don't have more than 10 games open
		if (count($user->games->creator) > $GAME['MAX_CREATOR_PUZZLES']){
			//they can only have one game open at a time
			return Shared\Errors::handleError("toomanycreators");
		}
		//check to make sure the user has enough money for the reward + the creation fee
		if ((intval(Input::get('reward')) + $sessionPuzzle->fees->creation) > $user->wallet->available){
			return Shared\Errors::handleError("nofunds");
		}
		$sessionPuzzle->title = e(Input::get('title'));
		$sessionPuzzle->desc = e(Input::get('desc'));
		$sessionPuzzle->fees->entry = intval(Input::get('entry'));
		$sessionPuzzle->fees->reward = intval(Input::get('reward'));
		Session::put('puzzle', $sessionPuzzle);
		$data = array('fees' => $sessionPuzzle->fees,
					  'wallet' => $user->wallet);
		return View::make('game.creator-confirm', $data);
	}
	
	public function savePuzzle(){
		$GAME = Config::get('game');
		if (!Session::has("puzzle")){
			//there's no puzzle session, probably because they just created it and hit back.  I call this "Caleb Syndrome"
			return Shared\Errors::handleError("cantmakepuzzle");
		}
		$sessionPuzzle = Session::get('puzzle');
		//the next two checks are duplicates from confirmCreate, but if they skip it somehow, this should weed them out
		//check for min char count on title and desc
		if (strlen($sessionPuzzle->title) <= 2 || strlen($sessionPuzzle->desc) <= 2){
			return Shared\Errors::handleError("badwords");
		}
		$user = CouchDB::getDoc(Session::get('user'), "users");
		//check to make sure they don't have more than 10 games open
		if (count($user->games->creator) > $GAME['MAX_CREATOR_PUZZLES']){
			//they can only have one game open at a time
			return Shared\Errors::handleError("toomanycreators");
		}
		Session::forget('puzzle');
		$response = CouchDB::setDoc($sessionPuzzle, "puzzles");
		array_push($user->games->creator, $response->id);
		$userresponse = CouchDB::setDoc($user, "users");
		$reward = Shared\Game::lockFunds(Session::get('user'), $sessionPuzzle->fees->reward);
		$creation = Shared\Game::payCreationFee(Session::get('user'), $sessionPuzzle->fees->creation);
		$data = array('reward' => $reward,
					  'creation' => $creation,
					  'id' => $response->id);
		return View::make('game.creator-save', $data);
	}
	
	public function closeGame($id){
		$user = CouchDB::getDoc(Session::get('user'), "users");
		if (!in_array($id, $user->games->creator)){
			//the id they passed isnt in their array
			return Shared\Errors::handleError("notcreator");
		}
		$puzzle = CouchDB::getDoc($id, "puzzles");
		if ($puzzle->stats->attempts != 0 && $puzzle->stats->solved == false){
			//the puzzle has attempts and isn't closed
			return Shared\Errors::handleError("cantclose");
		}
		$index = array_search($id, $user->games->creator);
		unset($user->games->creator[$index]);
		$response = CouchDB::setDoc($user, "users");
		if ($puzzle->stats->solved == false){
			//this person needs a refund of the reward
			$amount = Shared\Game::unlockFunds($user->_id, $puzzle->fees->reward);
			$net = 0 - $puzzle->fees->creation;
		}else{
			//they don't get a refund. so sad
			$amount = 0;
			$gross = $puzzle->stats->attempts * $puzzle->fees->entry;
			$net = ($gross - ($puzzle->stats->attempts * $puzzle->fees->creation)) - $puzzle->fees->reward - $puzzle->fees->creation;
		}
		//it passes the checks, so let's close it
		$response = CouchDB::deleteDoc($puzzle, "puzzles");
		$data = array('amount' => $amount,
					  'profit' => $net,
					  'puzzle' => $puzzle);
		return View::make('game.creator-close', $data);

	}
}