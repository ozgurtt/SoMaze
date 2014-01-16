<?php

class UserController extends BaseController {
	
	public function accountIndex(){
		$user = CouchDB::getDoc(Session::get('user'), "users");
		$data = array("user" => $user);
		return View::make('account.main', $data);
	}

	public function changeNickname(){
	error_log("change nickname str len: " . strlen(Input::get('nickname')));
		if (strlen(Input::get('nickname')) <= 2){
			return Shared\Errors::handleError("nonick");
		}
		$nickname = CouchDB::changeNickname(Input::get('nickname'));
		$data = array("nickname" => $nickname);
		return View::make('account.nickname', $data);
	}
}