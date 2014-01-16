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
	
	public function response($confirm){
		if ($confirm == true){
			//they confirmed and they are back on this page
			Session::flash('confirm', true);
			$data = array('id' => Session::get('id'));
			return Redirect::action('GameController@playGame', $data);
		}
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
		error_log("create puzzle, width: " . Input::get('width'));
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

}