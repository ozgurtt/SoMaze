<?php namespace Coins;

require_once 'easydogecoin.php';

class Dogecoin {

    public static function getInfo(){
    	//gets info about the current daemon running
    	$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->getinfo();
	    return $data;
	}
	
	public static function getBalance($id=""){
		//gets the balance for the server or for the user
		$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$available = $dogecoin->getbalance($id, $COINS['DOGE']['MIN_CONF']);
		$pending = $dogecoin->getbalance($id, 0) - $available;
		if ($id != "" && strlen($id) > 20){
			//not the default account
			$user = \CouchDB::getDoc($id, "users");
			$locked = $user->wallet->locked;
		}else{
			$locked = 0;
		}
		$data = array(
			'available' => $available,
			'pending'   => $pending,
			'locked'    => $locked);
	    return $data;
	}
	
	public static function getNewAddress($id){
		//creates a new account for the user specified
    	$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->getnewaddress($id);
	    return $data;
	}
	
	public static function getAccountAddress($id){
		//returns the current address for receiving payments
    	$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->getaccountaddress($id);
	    return $data;
	}
	
	public static function move($from, $to, $amount, $conf=1, $commment=""){
		//returns the current address for receiving payments
    	$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->move($from, $to, $amount, $conf);
	    return $data;
	}
	
	public static function sendFrom($account, $address, $amount){
		//sends coins out
		$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->sendfrom($account, $address, $amount);
	    return $data;
	}
	
	public static function listTransactions($account, $count=10, $from=0){
		//lists all transactions to this account
		$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->listtransactions($account, $count, $from);
	    return $data;
	}
	
	public static function getReceivedByAccount($account){
		//lists all received to this account
		$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->getreceivedbyaccount($account);
	    return $data;
	}
	
	public static function listReceivedByAccount($account){
		//lists all received by account (doesn't need account passed)
		$COINS = \Config::get('coins');
		$dogecoin = new \Dogecoin($COINS['DOGE']['USER'],$COINS['DOGE']['PASS'],$COINS['DOGE']['IP'],$COINS['DOGE']['PORT'],'http');
		$data = $dogecoin->listreceivedbyaccount();
	    return $data;
	}
	
	
	
	
	public static function payUser($from, $to, $amount, $fee){
		//used to pay users (for entry fees)
		$frombalance = Dogecoin::getBalance($from);
		if ($frombalance['available'] < $amount){
			//they don't have the funds
			return \Shared\Error::handleError("nofunds");
		}
		//move the fee to the fee account
		try{
			$response = Dogecoin::move($from, $to, $amount);
			$response = Dogecoin::move($to, "CREATION-FEE", $fee);
			return $amount;
		}catch(Exception $e){
			return 0;
		}
	}

	public static function rewardUser($from, $to, $amount, $fee=0){
		//used to reward users (for winning)
		$fromuser = \CouchDB::getDoc($from, "users");
		if ($fromuser->wallet->locked < $amount){
			//they don't have the funds
			return \Shared\Error::handleError("nofunds");
		}
		$fromuser->wallet->locked -= $amount;
		$response = \CouchDB::setDoc($fromuser, "users");
		//send the money
		try{
			$response = Dogecoin::move("LOCKED-FEE", $to, $amount);
			return $amount;
		}catch(Exception $e){
			return 0;
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
	}
	
	public static function lockFunds($from, $amount, $fee=0){
		//locks funds to prepare for reward
		$fromuser = \CouchDB::getDoc($from, "users");
		$frombalance = Dogecoin::getBalance($from);
		if ($frombalance['available'] < ($amount + $fee)){
			//they don't have the funds
			return \Shared\Error::handleError("nofunds");
		}else{
			if ($fee != 0){
				$response = Dogecoin::payMe($from, $fee);
			}
			$response = Dogecoin::move($from, "LOCKED-FEE", $amount);
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
			$response = Dogecoin::move("LOCKED-FEE", $from, $amount);
			$fromuser->wallet->locked -= $amount;
			$response = \CouchDB::setDoc($fromuser, "users");
			return $amount;
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
	}
	
	public static function payMe($from, $amount){
		//pay ME billy
		$fromuser = \CouchDB::getDoc($from, "users");
		$frombalance = Dogecoin::getBalance($from);
		if ($frombalance['available'] < $amount){
			//they don't have the funds
			return \Shared\Error::handleError("nofunds");
		}else{
			$response = Dogecoin::move($from, "CREATION-FEE", $amount);
			return $amount;
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
	}
	
		public static function coinUser($to, $amount){
		//pay a user from the creation fee account
		$frombalance = Dogecoin::getBalance('CREATION-FEE');
		if ($frombalance['available'] < $amount){
			//they don't have the funds
			return \Shared\Error::handleError("nofunds");
		}else{
			$response = Dogecoin::move("CREATION-FEE", $to, $amount);
			return $amount;
		}
		//something went wrong, but i have no idea what that might be.
		return 0;
	}

		
}

?>