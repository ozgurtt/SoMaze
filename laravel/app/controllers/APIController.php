<?php

class APIController extends BaseController {
	
	/*
	API methods that relate to creating and solving
	*/
	public function getTiles_All(){
		$data = CouchDB::getDoc("tiles", "misc");
		return Response::json($data);
	}
	
	/*
	API methods that only relate to being a solver
	*/
	public function getMap_Solver($id){
		$puzzle = CouchDB::getDoc($id, "puzzles");
		Session::put('puzzle', (clone $puzzle));
		//load the user
		$user = CouchDB::getDoc(Session::get('user'), "users");
		//if they have a game session for the map (which they should)
		if (Session::get('game') == $user->games->solver->$id){
			//load the game
			$game = CouchDB::getDoc(Session::get('game'), "games");
			$puzzle->hp = $game->hp;
			$puzzle->status = $game->status;
			//if they've moved at all
			if (count($game->movechain) != 0){
				//set their starting place to the last place they were
				$puzzle->map[end($game->movechain)] = 1;
			}
		}else{
			return Shared\Errors::handleError("notingame");
		}
		//loaded the map, now store it in the session and then convert it
		$data= Shared\Game::convertMap($puzzle, $game);
		return Response::json($data);
	}
	
	public function getMove_Solver($id, $tileID, $sessionID){
		//if the game var isn't set in the session
		if (!Session::has('game')){
			//it means that the user hasn't been loaded ever
			error_log("game session doesn't exist, get it from the user");
			$user = CouchDB::getDoc(Session::get('user'), "users");
			if (!isset($user->games->solver->$id)){
				//user isn't a solver in this game
				return Shared\Errors::handleError("notingame");
			}
			Session::put('game', $user->games->solver->$id);
		}
		//user is a solver, so let's load the game
		$game = CouchDB::getDoc(Session::get('game'), "games");
		if ($game->gameid != $id){
			//session game id is stale, update it
			$user = CouchDB::getDoc(Session::get('user'), "users");
			if (!isset($user->games->solver->$id)){
				//user isn't a solver in this game
				return Shared\Errors::handleError("notingame");
			}	
			//set the game id into the session var
			Session::put('game', $user->games->solver->$id);
			//get the new game that was just got from the user
			$game = CouchDB::getDoc(Session::get('game'), "games");
		}
		//check to make sure the puzzle isn't stale
		if (Session::get('puzzle')->_id != $id){
			error_log("puzzle is stale, getting it again");
			//the puzzle is stale, let's get a new copy of it
			$puzzle = CouchDB::getDoc($id, "puzzles");
			Session::put('puzzle', (clone $puzzle));
		}
		if ($game->hp <= 0){return Shared\Errors::handleError("youredead");}
		$data = Shared\Game::convertMove($game, Session::get('puzzle'), $tileID, $sessionID);
		return Response::json($data);
	}
	
	/*
	API methods that only relate to being a creator
	*/
	public function getMap_Creator($width, $height){
		$data= Session::get('puzzle');
		return Response::json($data);
	}
	
	public function evalMap_Creator(){
		$puzzle = json_decode(file_get_contents("php://input"), true);
		$data = new stdClass();
		if (Shared\Game::isValid($puzzle) == true){
			$data->valid = true;
			$data->fee = Shared\Game::scoreMap($puzzle);
			$sessionPuzzle = Session::get('puzzle');
			$sessionPuzzle->map = $puzzle['map'];
			$sessionPuzzle->traps = Shared\Game::populateTraps($puzzle);
			$sessionPuzzle->start = array_search(1, $sessionPuzzle->map);
			$sessionPuzzle->end = array_search(2, $sessionPuzzle->map);
			$sessionPuzzle->fees->creation = $data->fee;
			Session::put('puzzle', $sessionPuzzle);
		}else{
			$data->valid = false;
		}
		return Response::json($data);
	}
	
	public function saveMap_Creator(){
	//to be added later
		$data= array("comingsoon" => "notyet");
		return Response::json($data);
	}
	
	//coin testing
	
	public function getCoinInfo(){
		$data = Coins\Dogecoin::getBalance();
		return Response::json($data);
	}
	
	public function missingMethod($parameters = array())
	{
	    return Reponse::json(array("error" => "missing method"));
	}

}