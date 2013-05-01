<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/Challenge.class.php");
	
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
						"status"=>$status
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
		
		$database = DataLayer::getChallengeInstance();
		
		if(!in_array($newStatus, ChallengeDataLayer::$STATUS)){
			return "The status '".$newStatus."' is not a valid status.";
		}
		
		if(SecurityToken::checkId($challengerID)){
			if($newStatus !== "PENDING" && $newStatus !== "CANCELLED"){
				// Trying to change status to one that should only be set by the challengee
				return "Only the person you are challenging can set the status to '".$newStatus."'.";
			}
		}else if(SecurityToken::checkId($challengeeID)){
			if($newStatus === "PENDING"){
				// Trying to change status to one that should only be set by the challengee
				return "Only the person challenging you can set the status to 'PENDING'.";
			}
		}else{
			// Trying to change status of a challenge they are not a part of.
			return "You don't have permission to change the status of a challenge between players "
			  .$challengerID." and " . $challengeeID . ".";
		}
		
		$success = $database->updateChallengeStatus($challengerID, $challengeeID, $newStatus);
		
		if($success){
			return array("success" => true);
		}else{
			return "There was a database error changing the status of the challenge.";
		}
	}
		

/*


 challenge
        GET: getChallenges($playerID, $challenger = null)
                 getLobbyPlayers()
        POST: challenge($challengerID, $challengeeID)
                    updateChallengeStatus($challengerID, $challengeeID, $newStatus)


*/
?>
