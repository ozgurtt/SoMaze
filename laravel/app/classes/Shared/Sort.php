<?php namespace Shared;

class Sort {

    public static function sortObject($obj, $type){
	    //pass it the object, and it prepares it for sorting and sorts it
	    switch ($type){
		    case "difficulty-asc":
		    	//difficulting, ascending (lowest to highest)
		    	foreach ($obj as $k => $row){
			    	$difficulty = \Shared\Game::getDifficulty($row->value[3], $row->value[4]);
			    	$obj[$k]->difficulty = $difficulty['difficulty'];
		    	}
		    	usort($obj, function($a, $b){
			    	if($a->difficulty == $b->difficulty){return 0 ;} 
					return ($a->difficulty < $b->difficulty) ? -1 : 1;
		    	});
		    	break;
		    case "difficulty-desc":
		    	//difficulting, descending (highest to lowest)
		    	foreach ($obj as $k => $row){
			    	$difficulty = \Shared\Game::getDifficulty($row->value[3], $row->value[4]);
			    	$obj[$k]->difficulty = $difficulty['difficulty'];
		    	}
		    	usort($obj, function($a, $b){
			    	if($a->difficulty == $b->difficulty){return 0 ;} 
					return ($a->difficulty > $b->difficulty) ? -1 : 1;
		    	});
		    	break;
		    case "date-asc":
		    	//date created, ascending (lowest to highest)
		    	usort($obj, function($a, $b){
			    	if($a->value[0]->created == $b->value[0]->created){return 0 ;} 
					return ($a->value[0]->created < $b->value[0]->created) ? -1 : 1;
		    	});
		    	break;
		    case "date-desc":
		    	//date created, descending (highest to lowest)
		    	usort($obj, function($a, $b){
			    	if($a->value[0]->created == $b->value[0]->created){return 0 ;} 
					return ($a->value[0]->created > $b->value[0]->created) ? -1 : 1;
		    	});
		    	break;
		    case "attempts-asc":
		    	//attempts, ascending (lowest to highest)
		    	usort($obj, function($a, $b){
			    	if($a->value[6]->attempts == $b->value[6]->attempts){return 0 ;} 
					return ($a->value[6]->attempts < $b->value[6]->attempts) ? -1 : 1;
		    	});
		    	break;
		    case "attempts-desc":
		    	//attempts, descending (highest to lowest)
		    	usort($obj, function($a, $b){
			    	if($a->value[6]->attempts == $b->value[6]->attempts){return 0 ;} 
					return ($a->value[6]->attempts > $b->value[6]->attempts) ? -1 : 1;
		    	});
		    	break;
		    case "reward-desc":
		    	//reward amount, descending (highest to lowest)
		    	usort($obj, function($a, $b){
			    	if($a->value[5]->reward == $b->value[5]->reward){return 0 ;} 
					return ($a->value[5]->reward > $b->value[5]->reward) ? -1 : 1;
		    	});
		    	break;
		    case "entry-asc":
		    	//entry fee, ascending (lowest to highest)
		    	usort($obj, function($a, $b){
			    	if($a->value[5]->entry == $b->value[5]->entry){return 0 ;} 
					return ($a->value[5]->entry < $b->value[5]->entry) ? -1 : 1;
		    	});
		    	break;
	    }
	    return $obj;
    }
}