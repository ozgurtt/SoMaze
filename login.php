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
	    	$nickname = generateNickname();
	    	$aux =<<<EOT
	    	
<div class="panel panel-default">
<div class="panel-body">
Google doesn't provide us with a cool nickname for you to use, and since we figure you don't want to use your real name, we have provided you with a super awesome nickname to use for now.  You can feel free to change it in your account settings if you'd like.  Although honestly, why would you want to?<br><br>
<b>Nickname: $nickname</b>
</div>
</div>
</p>
EOT;
	    	$content =<<<EOT
<p>You've successfully logged in!<br>
$aux
Click <a href='index.php'>here</a> to go home</p>

EOT;
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
$body = str_replace("###HEADING###", "OpenID Login Status", $body);
$body = str_replace("###CONTENT###", $content, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;
die();

//functions start here!
function generateNickname(){
	//creates a super awesome nickname for you
	$adjectives = file('templates/adjectives.txt', FILE_IGNORE_NEW_LINES);
	$nouns = file('templates/nouns.txt', FILE_IGNORE_NEW_LINES);
	return ucwords($adjectives[array_rand($adjectives)] . " " . $nouns[array_rand($nouns)]) . " " . strval(rand(1,100));
}

?>