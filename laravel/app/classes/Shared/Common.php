<?php namespace Shared;

class Common {

    public static function generateSession(){
		$bytes = openssl_random_pseudo_bytes(8, $strong);
	    $hex = bin2hex($bytes);
	    return $hex;
	}
	
	public static function timeElapsed($ptime) {
	    $etime = time() - $ptime;
	    
	    if ($etime < 1) {
	        return '0 seconds';
	    }
	    
	    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
	                30 * 24 * 60 * 60       =>  'month',
	                24 * 60 * 60            =>  'day',
	                60 * 60                 =>  'hour',
	                60                      =>  'minute',
	                1                       =>  'second'
	                );
	    
	    foreach ($a as $secs => $str) {
	        $d = $etime / $secs;
	        if ($d >= 1) {
	            $r = round($d);
	            return $r . ' ' . $str . ($r > 1 ? 's' : '');
	        }
	    }
	}
	
}