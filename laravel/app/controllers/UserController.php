<?php

class UserController extends BaseController {
	
	public function accountIndex(){
		$needRefresh = false;
		$user = CouchDB::getDoc(Session::get('user'), "users");
		//check for stale games
		$solverGames = get_object_vars($user->games->solver);
		foreach ($solverGames as $name => $game){
			//go through each game and make sure it still exists
			$puzzle = CouchDB::getDoc($name, "puzzles"); 
			//if it's bad, get rid of it!
			if (count(get_object_vars($puzzle)) == 0){
				$game = CouchDB::getDoc($user->games->solver->{$name}, "games");
				unset($user->games->solver->{$name});
				$response = CouchDB::deleteDoc($game, "games");
				$needRefresh = true;
			}
		}
		if ($needRefresh == true){
			$response = CouchDB::setDoc($user, "users");
		}
		$data = array("user" => $user);
		return View::make('account.main', $data);
	}

	public function changeNickname(){
		if (strlen(Input::get('nickname')) <= 2 || strlen(Input::get('nickname')) > 100){
			return Shared\Errors::handleError("nonick");
		}
		$nickname = CouchDB::changeNickname(Input::get('nickname'));
		$data = array("nickname" => $nickname);
		return View::make('account.nickname', $data);
	}
	
	public function showWallet(){
		$user = CouchDB::getDoc(Session::get('user'), "users");
		$data = array("user" => $user);
		return View::make('account.wallet', $data);
	}
	
	public function getNewAddress(){
		//gets a new address for the user
		$data = Coins\Dogecoin::getNewAddress(Session::get('user'));
		return Redirect::action('UserController@showWallet', array("newAddress" => $data));
	}
}