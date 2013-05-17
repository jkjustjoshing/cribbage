<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/Challenge.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/Heartbeat.class.php");
	
	function getChallenges($data){ //$playerID, $challenger = null){
		$playerID = intval($data["playerID"]);
		if(isset($data["challenger"])){
			$challenger = $data["challenger"];
		}
		
		if(!SecurityToken::checkId($playerID)){
			return "You don't have permission to view this user's challenges.";
		}
		
		$challenges = Challenge::getChallenges($playerID, $challenger);
		/*
					$challengeItem = array(
						"challengerID"=>$challengerID, 
						"challengeeID"=>$challengeeID, 
						"status"=>$status,
						"gameID"=>gameID // when the status is "ACCEPTED"
					);
		*/
		if($challenges === false){
			return "There was an error fetching the challenges.";
		}
		
		return $challenges;
		
	}
	
	function challenge($data){//$challengerID, $challengeeID){
		$challengeeID = intval($data["challengeeID"]);
		$challengerID = intval($data["challengerID"]);
				
		if(!SecurityToken::checkId($challengerID)){
			return "You don't have permission to challenge as user id ".$challengerID.".";
		}
		
		if($challengeeID == $challengerID){
			return "You can't challenge yourself.";
		}
		
		$success = Challenge::challenge($challengerID, $challengeeID);
		
		if($success){
			return array("success" => true);
		}else{
			return "There was a database error creating the challenge.";
		}
		
	}

	

	function updateChallengeStatus($data){//$challengerID, $challengeeID, $newStatus){
		$challengerID = intval($data["challengerID"]);
		$challengeeID = intval($data["challengeeID"]);
		$newStatus = $data["newStatus"];
		$playerID = SecurityToken::extract();

		$database = DataLayer::getChallengeInstance();
		
		if(!in_array($newStatus, ChallengeDataLayer::$STATUS)){
			return "The status '".$newStatus."' is not a valid status.";
		}
		
		if($playerID === $challengerID){
			if($newStatus !== "PENDING" && $newStatus !== "CANCELLED"){
				// Trying to change status to one that should only be set by the challengee
				return "Only the person you are challenging can set the status to '".$newStatus."'.";
			}
		}else if($playerID === $challengeeID){
			if($newStatus === "PENDING"){
				// Trying to change status to one that should only be set by the challengee
				return "Only the person challenging you can set the status to 'PENDING'.";
			}
		}else{
			// Trying to change status of a challenge they are not a part of.
			return "You don't have permission to change the status of a challenge between players "
			  .$challengerID." and " . $challengeeID . ".";
		}

		$otherPlayerID = $challengerID+$challengeeID-$playerID;
		if(!$database->isUserHere($otherPlayerID, 0)){// Is other user even in the lobby?
			$database->updateChallengeStatus($challengerID, $challengeeID, "CANCELLED");
			return "Player " . $otherPlayerID . " is no longer online.";
		}
		
		$success = $database->updateChallengeStatus($challengerID, $challengeeID, $newStatus);
		
		if($success){
			if($newStatus == "ACCEPTED") return array("success" => true, "gameID" => Gamespace::createGame($challengeeID, $challengerID));
			else return array("success" => true);
		}else{
			return "There was a database error changing the status of the challenge.";
		}
	}
		

	function getOnlinePlayers($data){
		$room = $data["room"];

		return Heartbeat::getOnlinePlayers($room);
	}
?>
