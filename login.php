<?php

require_once 'lib/openid.php';

//common vars and such
require_once "lib/common.php";
date_default_timezone_set('America/Chicago');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isset($_GET['logout'])){
	session_destroy();
	$content = "<p>You have been logged out.  Many destroyed sessions. <br>Click <a href='index.php'>here</a> to go home</p>";
}else{
	$content = '';
	try {
	    # Change 'localhost' to your domain name.
	    $openid = new LightOpenID($DOMAIN);
	    if(!$openid->mode) {
	        if(isset($_GET['login'])) {
	        	$openid->required = array('namePerson/friendly');
	            $openid->identity = 'https://www.google.com/accounts/o8/id';
	            header('Location: ' . $openid->authUrl());
	        }
	    } elseif($openid->mode == 'cancel') {
		    $content =  "<p>You cancelled the log in process. Such disappoint.  <br>Click <a href='index.php'>here</a> to go home</p>";
	    } else {
	    	if ($openid->validate()){
		    	//user is validated
		    	$client = new couchClient ($DB_ROOT,"users");
				try{
					$user = $client->getDoc($openid->identity);
				}
				catch (Exception $c){
					//user wasn't found, don't throw an error, just make them!
					$user = createUser($openid->identity);
					$reponse = setDoc($user, "users");	
					$nickname = $user->nickname;
				}
		    	$_SESSION['user'] = $openid->identity;
		    	$_SESSION['nickname'] = $user->nickname;
		    	if (isset($nickname)){
		    	$aux =<<<EOT
	<div class="panel panel-default">
	<div class="panel-body">
	Google doesn't provide us with a cool nickname for you to use, and since we figure you don't want to use your real name, we have provided you with a super awesome nickname to use for now.  You can feel free to change it in your account settings if you'd like.  Although honestly, why would you want to?<br><br>
	<b>Nickname: $nickname</b>
	</div>
	</div>
	</p>
EOT;
				}else{$aux="";}
		    	$content =<<<EOT
	<p>You've successfully logged in!<br>
	$aux
	Click <a href='index.php'>here</a> to go home</p>
	
EOT;
	    	}else{
		    	//user isn't valided
		    	$content =  "<p>Something went wrong during the log in process.  Much sad.  <br>Click <a href='index.php'>here</a> to go home</p>";
	    	}
	        //echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';
	        //echo "<br>" . json_encode($openid->getAttributes());
	    }
	} catch(ErrorException $e) {
	    handleError("badlogin", $e->getMessage());
	}
}
$body = formatLogin($body);

$body = str_replace("###HEADING###", "OpenID Login Status", $body);
$body = str_replace("###CONTENT###", $content, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;
die();

function generateNickname(){
	//creates a super awesome nickname for you
	$adjectives = file('templates/adjectives.txt', FILE_IGNORE_NEW_LINES);
	$nouns = file('templates/nouns.txt', FILE_IGNORE_NEW_LINES);
	return ucwords($adjectives[array_rand($adjectives)] . " " . $nouns[array_rand($nouns)]) . " " . strval(rand(1,100));
}

function createUser($id){
	$user = new stdClass();
	$user->_id = $id;
	$user->nickname = generateNickname();
	$user->joined = time();
	$user->wallet = new stdClass();
	$user->wallet->available = 0;
	$user->wallet->pending = 0;
	$user->wallet->locked = 0;
	$user->stats = new stdClass();
	$user->stats->attempts = 0;
	$user->stats->wins = 0;
	$user->stats->losses = 0;
	$user->games = new stdClass();
	$user->games->creator = array();
	$user->games->solver = new stdClass();
	return $user;
}

?>