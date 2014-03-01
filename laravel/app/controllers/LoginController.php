<?php
require_once "lib/openid.php";
require_once "lib/couch.php";
require_once "lib/couchClient.php";
require_once "lib/couchDocument.php";

class LoginController extends BaseController {

	public function doLogin(){
		$COMMON = Config::get('common');
		try{
			$openid = new LightOpenID($COMMON['DOMAIN']);
			error_log("login called");
		    if(!$openid->mode) {
		        //if($login == true) {
		        	error_log("login called, login true");
		        	$openid->required = array('namePerson/friendly');
		            $openid->identity = 'https://www.google.com/accounts/o8/id';
		            //header('Location: ' . $openid->authUrl());
		            return Response::make( '', 302 )->header( 'Location', $openid->authUrl() );
		        //}else{error_log("login called, login false");}
		    } elseif($openid->mode == 'cancel') {
				return View::make('login.cancel');
		    } else {
		    error_log("login validating");
		    	if ($openid->validate()){
			    	//user is validated
					try{
						$client = new couchClient ($COMMON['DB_ROOT'],"users");
						$username = substr($openid->identity, strpos($openid->identity, "=") + 1);
						$user = $client->getDoc($username);
					}
					catch (Exception $c){
						//user wasn't found, don't throw an error, just make them!
						$username = substr($openid->identity, strpos($openid->identity, "=") + 1);
						$user = CouchDB::createUser($username);
						$nickname = $user->nickname;
						$address = Coins\Dogecoin::getNewAddress($username);
					}
					Session::put('user', $username);
					Session::put('nickname', $user->nickname);
					Session::put('status', $user->status);
					Session::put('ip', $_SERVER['REMOTE_ADDR']);
			    	if (isset($nickname)){
			    		//we gave them a nickname (first sign in)
			    		return View::make('login.success', array("nickname" => $nickname));
			    	}else{
			    		//they already have a nickname
			    		return View::make('login.success', array("nickname" => null));
			    	}
		    	}else{
			    	//user isn't valided
			    	return View::make('login.failed');
		    	}
		    }
		} catch(ErrorException $e) {
			return Shared\Errors::handleError("badlogin", $e->getMessage());
		}
	}
	
	public function doLogout(){
		Session::flush();
		return View::make('login.logout');
	}

}