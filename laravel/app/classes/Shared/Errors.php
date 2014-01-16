<?php namespace Shared;

class Errors {

	public static function handleError($error, $meta=null){
		switch($error){
			case "noid":
				$error = "No Game ID";
				$content = "You didn't provide a game ID!";
				break;
			case "nocommand":
				$error = "No API Command";
				$content = "To use the API, you must provide a command!";
				break;
			case "nodoc":
				$error = "Document Not Found";
				$content = "No document found with ID: " . $meta . "";
				break;
			case "noview":
				$error = "View Not Found";
				$content = "The view you requested can not be found";
				break;
			case "notile-move":
				$error = "No Tile Given";
				$content = "The move command requires a tile the player is moving to";
				break;
			case "nosession":
				$error = "No Session ID Given";
				$content = "Session IDs are required for this request";
				break;
			case "badsave":
				$error = "Unable to save document";
				$content = "Save failed with document ID: " . $meta . "";
				break;
			case "badrm":
				$error = "Unable to delete document";
				$content = "Delete failed with document ID: " . $meta . "";
				break;
			case "badlogin":
				$error = "Unable to log you in";
				$content = "Something went wrong during the OpenID login: (" .  $meta . ")";
				break;
			case "nonick":
				$error = "No/Bad nickname provided";
				$content = "To change your nickname, you must provide a nickname and it must be at least 3 characters long!";
				break;
			case "notingame":
				$error = "You are not in this game";
				$content = "You can't submit commands to a game that you haven't joined.";
				break;
			case "mustconfirm":
				$error = "Confirmation needed";
				$content = "You must confirm that you're willing to pay the entry fee before you can play this game.";
				break;
			case "notloggedin":
				$error = "You are not logged in";
				$content = "You must be logged in to do that.";
				break;
			case "nofunds":
				$error = "Not enough money";
				$content = "You have insufficient funds to do this.";
				break;
			case "badparams":
				$error = "Bad parameters";
				$content = "The parameters you supplied are invalid.";
				break;
			case "youredead":
				$error = "You're dead, you can't do things";
				$content = "You're a ghost, you can't be going around and doing things.  Stick to being spooky.  Much scared.";
			case "badwords":
				$error = "Bad title/description";
				$content = "The title and description for your game need to be at least 3 characters each.";
				break;
			case "lowentry":
				$error = "Entry fee too low";
				$content = "Your entry fee needs to be at least as much as your creation fee.  You would lose money the other way.";
			case "lowreward":
				$error = "Reward too low";
				$content = "Your reward has to be at least as much as your entry fee.";
				break;
			default:
				$content = "No listing for error: " . $error;
				$error = "Generic Error";
				break;
		}
		error_log("handleError: " . $error . " - " . $content);
		$data = array("error" => $error,
					  "content" => $content);
		return \View::make('error', $data);
	}
}