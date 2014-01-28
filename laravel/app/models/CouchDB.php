<?php
require_once "lib/couch.php";
require_once "lib/couchClient.php";
require_once "lib/couchDocument.php";

class CouchDB {

	public static function getDoc($id, $db)
	{
		$COMMON = Config::get('common');
		//gets a document from the database
		$client = new couchClient ($COMMON['DB_ROOT'],$db);
		try{
			return $client->getDoc($id);
		}
		catch (Exception $e){
			//doc
			return Shared\Errors::handleError("nodoc", $id . " db: " . $db . (($COMMON['VERBOSE'] == true)?" - " . json_encode($e):""));
		}
	}
	
	public static function setDoc($id, $db){
		$COMMON = Config::get('common');
		//stores a document in the database
		$client = new couchClient ($COMMON['DB_ROOT'],$db);
		try {
			return $client->storeDoc($id);
		} catch (Exception $e) {
			return Shared\Errors::handleError("badsave", $id->_id . " db: " . $db . (($COMMON['VERBOSE'] == true)?" - " . $e:""));
		}	
	}
	
	public static function deleteDoc($id, $db){
		$COMMON = Config::get('common');
		//stores a document in the database
		$client = new couchClient ($COMMON['DB_ROOT'],$db);
		try {
			return $client->deleteDoc($id);
		} catch (Exception $e) {
			return Shared\Errors::handleError("badrm", $id->_id . " db: " . $db . (($COMMON['VERBOSE'] == true)?" - " . json_encode($e):""));
		}	
	}
	
	public static function getView($folder, $id, $db){
		$COMMON = Config::get('common');
		$client = new couchClient ($COMMON['DB_ROOT'],$db);
		try{
			return $client->getView($folder,$id);
		}
		catch (Exception $c){
			//map wasn't found
			return Shared\Errors::handleError("noview", $id->_id . " db: " . $db . (($COMMON['VERBOSE'] == true)?" - " . json_encode($e):""));
		}
	}
	
	public static function createUser($id){
		$user = new stdClass();
		$user->_id = $id;
		$user->nickname = CouchDB::generateNickname();
		$user->joined = time();
		$user->wallet = new stdClass();
		$user->wallet->locked = 0;
		$user->wallet->payout = '';
		$user->stats = new stdClass();
		$user->stats->attempts = 0;
		$user->stats->wins = 0;
		$user->stats->losses = 0;
		$user->status = CouchDB::createUserStats();
		$user->games = new stdClass();
		$user->games->creator = array();
		$user->games->solver = new stdClass();
		$reponse = CouchDB::setDoc($user, "users");	
		return $user;
	}
	
	public static function createGame($start, $id, $user){
		//creates a new blank game and returns it
		$game = new stdClass();
		$game->_id = $id . " - " . $user;
		$game->gameid = $id;
		$game->userid = $user;
		$game->hp = 100;
		$game->status = array();
		$game->started = time();
		$game->sessionID = Shared\Common::generateSession();
		$game->movechain = array();
		array_push($game->movechain, $start);
		$response = CouchDB::setDoc($game, "games");
		return $game;
	}
	
	public static function createPuzzle($width, $height){
		$COMMON = Config::get('common');
		$returnObj = new stdClass();
		$returnObj->active = false;
		$returnObj->creator = CouchDB::createCreator();
		$returnObj->dimensions = new stdClass();
		$returnObj->fees = new stdClass();
		$returnObj->traps = new stdClass();
		$returnObj->stats = new stdClass();
		//stats object
		$returnObj->stats->solved = false;
		$returnObj->stats->attempts = 0;
		$returnObj->stats->last = null;
		$returnObj->stats->winner = null;
		$returnObj->stats->windate = null;
		$returnObj->stats->winnick = null;
		$returnObj->stats->winstatus = null;
		$returnObj->dimensions->width = intval($width);
		$returnObj->dimensions->height = intval($height);
		$returnObj->map = array_fill(0, ((intval($width) * intval($height))), 0);
		$returnObj->currency = $COMMON['CURRENCY'];
		return $returnObj;
	}
		
	public static function createUserStats(){
		//makes a user stats object
		$userstats = new stdClass();
		$userstats->verified = false;
		$userstats->vip = false;
		$userstats->donator = false;
		$userstats->staff = false;
		return $userstats;
	}
	
	public static function createCreator(){
		//makes a creator object
		$creator = new stdClass();
		$creator->id = Session::get('user');
		$creator->nickname = Session::get('nickname');
		$creator->created = time();
		$creator->status = Session::get('status');
		return $creator;
	}

	public static function generateNickname(){
		//creates a super awesome nickname for you
		$adjectives = CouchDB::getDoc("adjectives", "misc");
		$nouns = CouchDB::getDoc("nouns", "misc");
		return ucwords($adjectives->words[array_rand($adjectives->words)] . " " . $nouns->words[array_rand($nouns->words)]) . " " . strval(rand(1,100));
	}
	
	public static function changeNickname($nickname){
		$user = CouchDB::getDoc(Session::get('user'), "users");
		$user->nickname = $nickname;
		$response = CouchDB::setDoc($user, "users");
		Session::put('nickname', $nickname);
		return $nickname;
	}

}