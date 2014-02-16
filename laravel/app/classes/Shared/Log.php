<?php namespace Shared;

class Log {

	public static function doLog($message, $type="info"){
		switch ($type){
			case "info":
				\Log::info($message);
				break;
			case "notice":
				\Log::notice($message);
				break;
			case "error":
				\Log::error($message);
				break;
		}
	}

	
}