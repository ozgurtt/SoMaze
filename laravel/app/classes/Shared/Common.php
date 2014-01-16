<?php namespace Shared;

class Common {

    public static function generateSession(){
		$bytes = openssl_random_pseudo_bytes(8, $strong);
	    $hex = bin2hex($bytes);
	    return $hex;
	}
	
	
}