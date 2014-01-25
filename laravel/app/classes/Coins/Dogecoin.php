<?php namespace Coins;

require_once 'easydogecoin.php';

class Dogecoin {

    public static function getInfo(){
    	$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->getinfo();
	    return $data;
	}
	
	public static function getBalance($id=null){
		$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		if ($id == null){
			//get serverwide balance
			$data = $dogecoin->getbalance();
		}else{
			$data = $dogecoin->getbalance($id);
		}
	    return $data;
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
	
	public static function unlockFunds($from, $amount){
		//locks funds to prepare for reward
		$fromuser = \CouchDB::getDoc($from, "users");
		if ($fromuser->wallet->locked < $amount){
			//you broke dawg
			return \Shared\Error::handleError("nofunds");
		}else{
			$fromuser->wallet->locked -= $amount;
			$fromuser->wallet->available += $amount;
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

		
}

?>