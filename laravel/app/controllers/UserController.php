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
		$transactions = Coins\Dogecoin::listTransactions($user->_id, 30);
		$data = array("user" => $user,
					  "transactions" => $transactions);
		return View::make('account.wallet', $data);
	}
	
	public function getNewAddress(){
		//gets a new address for the user
		$data = Coins\Dogecoin::getNewAddress(Session::get('user'));
		return Redirect::action('UserController@showWallet', array("newAddress" => $data));
	}
	
	public function withdraw(){
		//sends funds to a user's address
		if (!Input::has('amount') || !Input::has('address')){
			//user didn't supply us with an amount or an address
			return Shared\Errors::handleError("badparams");
		}
		$COMMON = Config::get('common');
		$COINS = Config::get('coins');
		$balance = Coins\Dogecoin::getBalance(Session::get('user'));
		if (floatval(Input::get('amount')) + $COINS[$COMMON['CURRENCY']]['TX_FEE'] > $balance['available']){
			//they are trying to do too much
			return Shared\Errors::handleError("nofunds");
		}
		$transid = Coins\Dogecoin::sendFrom(Session::get('user'), Input::get('address'), Input::get('amount'));
		if ($transid == false){
			//something went wrong
			return Shared\Errors::handleError("withdrawfailed");
		}
		$data = array("amount"  => Input::get('amount'),
					  "address" => Input::get('address'),
					  "transid" => $transid);
		//(D[1-9a-z]{20,40}) regex for dogecoin address
		return View::make('account.withdraw', $data);
	}
	
	public function publicListing(){
		$page = Input::get('page', 1);
		$perPage = 10;
		$results = CouchDB::getView("listing", "allusers", "users");
		//do the sorting here
		//error_log(json_encode($results->rows[0]));
		$users = array_chunk($results->rows, $perPage);
		$paginator = Paginator::make($users[$page-1], count($results->rows), $perPage);
		$data = array('results' => $paginator,
					  'count'   => count($results->rows));
		return View::make('account.public-listing', $data);
	}
	
	public function publicProfile($id){
		$user = CouchDB::getDoc(e($id), "users");
		$data = array("user" => $user);
		return View::make('account.public-profile', $data);
	}
}