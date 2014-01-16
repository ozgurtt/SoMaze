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
		$user = CouchDB::getDoc(Session::get('user'), "users");
		$puzzle = CouchDB::getDoc($id, "puzzles");
		if (isset($user->games->solver->$id)){
			//this person has already paid
			Session::put('game', $user->games->solver->$id);
			$game = CouchDB::getDoc(Session::get('game'), "games");
			$amount = 0;
		}else{
			//create the game in their active games and save it
			$game = CouchDB::createGame($puzzle->start, $id, Session::get('user'));
			$user->games->solver->$id = $game->_id;
			$response = CouchDB::setDoc($user, "users");
			//then set the session
			Session::put('game', $user->games->solver->$id);
			//change them for entry
			$amount = Shared\Game::payUser(Session::get('user'), $puzzle->creator, $puzzle->fees->entry, $puzzle->fees->creation);
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
		//check for min char count on title and desc
		if (strlen(Input::get('title')) <= 2 || strlen(Input::get('desc')) <= 2){
			return Shared\Errors::handleError("badwords");
		}
		//load the puzzle
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
		$sessionPuzzle = Session::get('puzzle');
		$response = CouchDB::setDoc($sessionPuzzle, "puzzles");
		$user = CouchDB::getDoc(Session::get('user'), "users");
		array_push($user->games->creator, $response->id);
		$userresponse = CouchDB::setDoc($user, "users");
		$reward = Shared\Game::lockFunds(Session::get('user'), $sessionPuzzle->fees->reward);
		$creation = Shared\Game::payCreationFee(Session::get('user'), $sessionPuzzle->fees->creation);
		$data = array('reward' => $reward,
					  'creation' => $creation,
					  'id' => $response->id);
		return View::make('game.creator-save', $data);
	}
}