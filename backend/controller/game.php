<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/gameplay/Gamespace.class.php");


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

		//Get if this game it's the user's turn
		try{
			$gamespace = new Gamespace($gameID, $userID);
		}catch(Exception $e){
			return "Player " + $userID + " doesn't have access to gameID " . $gameID . ".";
 		}



	}

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

/*
"getTurn", // Are we waiting for the other user to do something (either put a card down, put cards in crib, accept points they are viewing)
"getDealer", // Returns if I am the dealer
"getCutCard", // Gets the cut card, or null if there isn't one
"getPeggingCards", // Get the cards for pegging
"getScore", // Get the scores of the two players
"getDealer", // Who is the dealer now?
"getGameData", // A large array of everything you could want to know about the board state
"pickCutIndex",  // Send an index of 
"shuffle", // Shuffle the deck. Pass the number of times to shuffle to reduce requests
"playCard", // Put a card on the table for pegging. Only remove from hand if it returns success (could return a required "go", a "not your turn", "you don't have that card")
"putInCrib", // Send 2 cards to put into the crib
"deal" */
?>
