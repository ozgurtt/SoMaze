<?
require_once "lib/couch.php";
require_once "lib/couchClient.php";
require_once "lib/couchDocument.php";

require_once 'lib/openid.php';

//common vars and such
require_once "lib/common.php";
date_default_timezone_set('America/Chicago');

session_start();
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
	    $content =  "<p>You cancelled the log in process. Such disappoint.  Click <a href='index.php'>here</a> to go home</p>";
    } else {
    	if ($openid->validate()){
	    	//user is validated
	    	$_SESSION['user'] = $openid->identity;
	    	//$userinfo = $openid->getAttributes();
	    	//$nickname = $userinfo['namePerson/friendly'];
	    	$content =  "<p>You've successfully logged in!  Click <a href='index.php'>here</a> to go home</p>";
    	}else{
	    	//user isn't valided
	    	$content =  "<p>Something went wrong during the log in process.  Much sad.  Click <a href='index.php'>here</a> to go home</p>";
    	}
        //echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';
        //echo "<br>" . json_encode($openid->getAttributes());
    }
} catch(ErrorException $e) {
    handleError("badlogin", $e->getMessage());
}

$body = str_replace("###LOGIN###", formatLogin(), $body);
$body = str_replace("###HEADING###", "Login", $body);
$body = str_replace("###CONTENT###", $content, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;

//functions start here!

?>