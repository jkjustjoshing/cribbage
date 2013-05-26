<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/gameplay/Gamespace.class.php");

	function deal($data){
		$gameID = intval($data["gameID"]);
		$numberOfTimesToShuffle = intval($data["numberOfTimesToShuffle"]);
		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " + $userID + " doesn't have access to gameID " . $gameID . ".";
 		}

		if($gamespace->gamestate !== "DEALING"){
			return "It's not time to deal!";
		}

		$return = $gamespace->deal($numberOfTimesToShuffle);
		
		if(strlen($return) !== 0){
			return $return;
		}else{
			return array("success" => true);
		}

	}

	function getGameData($data){
		$gameID = intval($data["gameID"]);
		
		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}
 		
 		$result = array();
 		$result["gameID"] = $gameID;
 		$result["scores"] = $gamespace->getScores();
 		$result["backPinPositions"] = $gamespace->getScores();

 		$myHand = $gamespace->getMyHand() === null ? array() : $gamespace->getMyHand()->cardArray();
 		$opponentHand = $gamespace->getOpponentHand() === null ? array() : $gamespace->getOpponentHand()->cardArray();
 		$crib = $gamespace->getCrib() === null ? array() : $gamespace->getCrib()->cardArray();		
		$result["hands"] = array(
				$playerID => $myHand,
				$gamespace->getOpponentID() => $opponentHand,
				"crib" => $crib
			);
		$result["dealer"] = $gamespace->dealerID;
		$result["turn"] = $gamespace->turnID;
		$result["gamestate"] = $gamespace->gamestate;
		$result["gamestatus"] = $gamespace->gamestatus;
		$result["cutCard"] = $gamespace->cutCard;
		$result["playedCards"] = $gamespace->getPlayedCards();
		
		return $result;
	}

	function getGameState($data){
		$gameID = intval($data["gameID"]);
		
		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

 		return array("gamestate" => $gamespace->gamestate);
	}


	function putInCrib($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

 		$card1 = new PlayingCard($data["cards"][0]["number"], $data["cards"][0]["suit"]);
 		$card2 = new PlayingCard($data["cards"][1]["number"], $data["cards"][1]["suit"]);

 		$result = $gamespace->putCardsInCrib($card1, $card2);
 		$crib = $gamespace->getCrib();

 		if($result == ""){
 			return array("success" => true,
 						 "cribSize" => count($crib->getCards()));
 		}else{
 			return $result;
 		}
	}

	function getHands($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

 		$myHand = $gamespace->getMyHand() === null ? array() : $gamespace->getMyHand()->cardArray();
 		$opponentHand = $gamespace->getOpponentHand() === null ? array() : $gamespace->getOpponentHand()->cardArray();
 		$crib = $gamespace->getCrib() === null ? array() : $gamespace->getCrib()->cardArray();		
		$result = array(
				$playerID => $myHand,
				$gamespace->getOpponentID() => $opponentHand,
				"crib" => $crib
			);

		return $result;
	}

	function pickCutIndex($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

 		$index = intval($data["index"]);
 		if($index < 0 || $index >= (52-6-6)){ // Number of cards in deck after dealing
 			return "Index " . $index . " is out of bounds.";
 		}

 		$result = $gamespace->cutCard($index);

 		if(!is_object($result)){
 			return $result;
 		}

 		$card = $gamespace->cutCard();

 		return array("cutCard"=>array("number"=>$card->getNumber(), "suit"=>$card->getSuit()));
	}

	function getCutCard($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

 		$card = $gamespace->cutCard();

 		return array("cutCard"=>array("number"=>$card->getNumber(), "suit"=>$card->getSuit()));
	}

	function playCard($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

 		$card = new PlayingCard($data["card"]["number"], $data["card"]["suit"]);
 		
 		$return = $gamespace->playCard($card);

 		if($return !== ""){
 			return $return;
 		}else{
 			//no error
 			return array("success"=>true, "gamestate" => $gamespace->gamestate);
 		}
	}

	/**
	 * getTurn($data)
	 * Method that returns an array saying whether or not it's the
	 * current user's turn. It is defined as being the user's turn
	 * if the player can do something and isn't waiting for the other
	 * user. Hypothetically, while both players are choosing what to 
	 * put in their crib, both players could have this method return
	 * true.
	 * 
	 * @param  [Array] $data Associative array with one index called gameID
	 * @return [Array] An array indicating success, or a failure message.
	 */
	function getTurn($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

		return array("turn"=>$gamespace->turnID);
	}

	function getPlayedCards($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

 		$cards = $gamespace->getPlayedCards();

 		return $cards;

	}

	function getDealer($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

		return array("dealer"=>$gamespace->dealerID);
	}

	
	function getScore($data){
		$gameID = intval($data["gameID"]);

		// Get security token to see who we are,
		$playerID = SecurityToken::extract();

		// Make sure user is allowed to see this game
		try{
			$gamespace = new Gamespace($gameID, $playerID);
		}catch(Exception $e){
			return "Player " . $playerID . " doesn't have access to gameID " . $gameID . ".";
 		}

		return array("scores"=>$gamespace->getScores(), "backPinPositions"=>$gamespace->getBackPinPositions());
	}

?>
