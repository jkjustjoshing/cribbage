<?php

	class GameplayDataLayer extends DataLayer{
		
		private $mysql;
		
		public function __construct($mysqli){
			$this->mysqli = $mysqli;
		}


		/**
		 * Gets the gameID for a game between these two players.
		 * If one doesn't exist create it.
		 * @param  int $player1ID One of the players
		 * @param  int $player2ID The other player
		 * @return int The gameID for a game between these two players, or false on error
		 */
		public function getGameID($player1ID, $player2ID){
			$sql = "SELECT id FROM gamespaces
			        WHERE ((player1ID=? AND player2ID=?) 
			        OR (player2ID=? AND player1ID=?)) 
					AND gamestatusID=(SELECT id FROM gamestatuses WHERE value='IN_PROGRESS')";

			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				$stmt->bind_param("iiii", $player1ID, $player2ID, $player1ID, $player2ID);
				$stmt->execute();
				
				$stmt->store_result();

				if($stmt->num_rows === 1){
					$stmt->bind_result($gameID);
					$stmt->fetch();
					return $gameID;
				}else{

					$sql = "INSERT INTO gamespaces
					        (player1ID, player2ID, turnID, dealerID, gamestatusID, gamestateID)
					        VALUES
					        (?,?,?,?,(
					        	SELECT id FROM gamestatuses WHERE value='IN_PROGRESS'
					        ),(
					        	SELECT id FROM gamestates WHERE value='DEALING'
					        ))";

					if($stmt = $this->mysqli->prepare($sql)){	
						//Bind parameter, execute, and bind result
						$stmt->bind_param("iiii", $player1ID, $player2ID, $player2ID, $player1ID);
						$stmt->execute();

						return $this->mysqli->insert_id;
					}
					return false;
				}
			}
			return false;
		}

		/**
		 * Get the basic game info from the database
		 * @param  int $gameID The game for whom to get the information from
		 * @return array An array of all the game's information
		 */
		public function getGameInfo($gameID){
			$sql = "SELECT game.player1ID, game.player2ID, 
				       game.player1Score, game.player2Score,
				       game.player1backPinPosition, game.player2backPinPosition,
				       game.turnID, game.dealerID, cards.suit, cards.number,
				       status.value,
				       state.value
				    FROM gamespaces AS game LEFT JOIN gamestatuses AS status
				    ON game.gamestatusID=status.id
				    LEFT JOIN gamestates AS state
				    ON game.gamestateID=state.id
				    LEFT JOIN playingcards AS cards
				    ON game.cutCard=cards.id
				    WHERE game.id=?";

			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				$stmt->bind_param("i", $gameID);
				$stmt->execute();
				$stmt->bind_result(
						$player1ID, $player2ID,
						$player1Score, $player2Score,
						$player1backPinPosition, $player2backPinPosition,
						$turnID, $dealerID, $cutCardSuit, $cutCardNumber,
						$gamestatus, $gamestate
					);
				
				$stmt->fetch();

				return array(
					"gameID"=>$gameID,
					"player1ID"=>$player1ID,
					"player2ID"=>$player2ID,
					"player1Score"=>$player1Score,
					"player2Score"=>$player2Score,
					"player1backPinPosition"=>$player1backPinPosition,
					"player2backPinPosition"=>$player2backPinPosition,
					"turnID"=>$turnID,
					"dealerID"=>$dealerID,
					"cutCard"=>array("suit"=>$cutCardSuit, "number"=>$cutCardNumber),
					"gamestatus"=>$gamestatus, // NOT the ID
					"gamestate"=>$gamestate  // NOT the ID

				);

			}
			return false;
		}	


		/**
		 * Change the game's state
		 * @param  int $gameID The game of whom to change the state of
		 * @param  string $newState The new state to change to. Must be existing state or exception thrown
		 * @return boolean If the operation successfully completed
		 */
		public function changeGameState($gameID, $newState){
			$sql = "UPDATE gamespaces
			        SET gamestateID=(
			        	SELECT id FROM gamestates WHERE value=?
			        )
			        WHERE id=?";

			if($stmt = $this->mysqli->prepare($sql)){
				//Bind parameter, execute, and bind result
				$stmt->bind_param("si", $newState, $gameID);
				return $stmt->execute();
			}

			return false;
		}

	
		/**
		 * Replace the hand in the database with the one passed in.
		 * @param  int $gameID   The game for which the hand belongs
		 * @param  int $playerID The player for which the hand is being written
		 * @param  array $cards    The cards to have in the hand
		 * @return boolean If the operation successfully completed
		 */
		public function writePlayerHand($gameID, $playerID, $cards){
			// First remove the hand if it exists

			$sql = "DELETE FROM playerhands
			        WHERE gameID=? AND playerID=?";
			if($stmt = $this->mysqli->prepare($sql)){
				//Bind parameter, execute, and bind result
				$stmt->bind_param("ii", $gameID, $playerID);
				if($stmt->execute() === false){
					//failure
					return false;
				}
			}else{
				return false;
			}
              
			// Then add the new hand
			$sql = "INSERT INTO playerhands (gameID, playerID, playingcardID, inHand)
					VALUES
					(?, ?, (
							SELECT id FROM playingcards WHERE suit=? AND number=?
						), ?)
			";
			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				for($i = 0; $i < count($cards); ++$i){
					$stmt->bind_param("iisii", $gameID, $playerID, $cards[$i]["suit"], $cards[$i]["number"], $cards[$i]["inHand"]);
					if(!$stmt->execute()){
						// Statement failed
						return false;
					}
				}
				return true;
			}else{
				return false;
			}
		
		}

		/**
		 * Gets each players' hand for the game,
		 * plus the crib. The business logic layer
		 * will restrict access as appropriate to this information 
		 * @param  int $gameID The game ID of the game from 
		 *                     which to get the hand data
		 * @return array An array of hands. The indeces will be the 
		 *               player's ID, and "crib" for the crib
		 */
		public function getHands($gameID){
			$sql = "SELECT 
					hand.playerID, hand.inHand,
					card.suit, card.number
					FROM playerhands AS hand
					LEFT JOIN playingcards AS card ON hand.playingcardID=card.id
					WHERE hand.gameID=?
					ORDER BY hand.playerID
			";
									
			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				$stmt->bind_param("i", $gameID);
				$stmt->execute();
				$stmt->bind_result($playerID, $inHand, $suit, $number);
				
				$handArr = array();
				while($stmt->fetch()){
					// If the playerID is null make it "crib"
					if($playerID === null){
						$playerID = "crib";
					}
					
					// If the hand array isn't made yet make it
					if(!isset($handArr[$playerID])){
						$handArr[$playerID] = array();
					}

					// Put in hand array
					$handArr[$playerID][] = array(
						"inHand" => $inHand,
						"suit" => $suit,
						"number" => $number
						);
				}
				return $handArr;
			}
			return false;
		}

		/**
		 * Put an array of cards in the crib for the given game
		 * @param  int $gameID The game in question
		 * @param  array $cards  The array of cards to add
		 * @return boolean If the add was successful
		 */
		public function putInCrib($gameID, $cards){
			// Then add the new hand
			$sql = "INSERT INTO playerhands (gameID, playerID, playingcardID)
					VALUES
					(?, NULL, (
							SELECT id FROM playingcards WHERE suit=? AND number=?
						))
			";
			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				for($i = 0; $i < count($cards); ++$i){
					$stmt->bind_param("isi", $gameID, $cards[$i]["suit"], $cards[$i]["number"]);
					if(!$stmt->execute()){
						// Statement failed
						return false;
					}
				}
				return true;
			}else{
				return false;
			}
		}

		/**
		 * Set the cut card for the given game.
		 * @param int $gameID The game ID for the game in question
		 * @param array $card Array representing the card being set as the cut card
		 * @return  boolean Whether or not the query was successful
		 */
		public function setCutCard($gameID, $card){
			// Then add the new hand
			$sql = "UPDATE gamespaces
					SET cutCard=(
						SELECT id FROM playingcards WHERE suit=? AND number=?
					)
					WHERE id=?
			";

			if($stmt = $this->mysqli->prepare($sql)){	
				$stmt->bind_param("sii", $card["suit"], $card["number"], $gameID);
				if(!$stmt->execute()){
					// Statement failed
					return false;
				}
				return true;
			}else{
				return false;
			}
		}

		/**
		 * Gets the cut card for the current game,
		 * or an empty array if no card has been cut
		 * @param  int $gameID The game ID for the game in question
		 * @return array  Array with the cut card, empty array for no cut card, and false for failure
		 */
		public function getCutCard($gameID){
			$sql = "SELECT 
					card.number, card.suit
					FROM gamespaces AS game
					LEFT JOIN playingcards AS card ON game.cutCard=card.id
					WHERE game.id=?
			";
									
			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				$stmt->bind_param("i", $gameID);
				$stmt->execute(); 
				$stmt->bind_result($number, $suit);
				$stmt->fetch();
				// echo ($card["number"] === null ? "null number " : "not null number");
				// echo ($card["suit"] === null ? "null suit" : "not null suit");
				// echo $gameID;
				// die();
				if($number !== null && $suit !== null){
					$card = array(
						"number" => $number,
						"suit" => $suit
						);
					return $card;
				}else{
				//	echo "other one";
				//	die();
					return array();
				}
			}
			return false;
		}

		/**
		 * Gets the card deck of the deckID given.
		 * Does not protect access to the deck since it's 
		 * only accessed server-side and through a
		 * Gamespace object.
		 * @param  int $deckID The ID of the deck to fetch
		 * @return array         An array consisting of array elements each with a suit and a number, or false on error
		 */
		public function getCardDeck($gameID){

			$sql = "SELECT 
					card.number AS num, card.suit AS suit
					FROM carddecks AS deck
					LEFT JOIN playingcards AS card ON deck.playingCardID=card.id
					WHERE deck.gameID=?
					ORDER BY deck.cardIndex ASC
			";
									
			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				$stmt->bind_param("i", $gameID);
				$stmt->execute();
				$stmt->bind_result($number, $suit);
				
				$cardArr = array();
				while($stmt->fetch()){
					$card = array(
						"number" => $number,
						"suit" => $suit
						);
					$cardArr[] = $card;
				}
				return $cardArr;
			}
			return false;
		}

		/**
		 * Delete a full deck from the database
		 * @param  int $gameID The game that the deck belongs to.
		 * @return boolean         Whether or not the delete was successful
		 */
		public function deleteCardDeck($gameID){
			$sql = "DELETE FROM carddecks WHERE gameID=?";
									
			if($stmt = $this->mysqli->prepare($sql)){
				//Bind parameter and execute
				$stmt->bind_param("i", $gameID);
				return $stmt->execute();
			}
			return false;
		}

		/**
		 * Insert a card deck into the database
		 * @param  int $gameID    The game that the deck belongs to.
		 * @param  array $cardArray An array of cards to insert (each card is an array with a "suit" and a "number" index)
		 * @return boolean            Whether or not the insert was successful
		 */
		public function insertCardDeck($gameID, $cardArray){

			$sql = "INSERT INTO carddecks (cardIndex, gameID, playingCardID)
					VALUES (?, ?, (
						SELECT id FROM playingcards WHERE suit=? AND number=?
						))";
									
			if($stmt = $this->mysqli->prepare($sql)){
				//Bind parameter and execute for each card
				for($i = 0; $i < count($cardArray); ++$i){
					$stmt->bind_param("iisi", $i, $gameID, $cardArray[$i]["suit"], $cardArray[$i]["number"]);
					if(!$stmt->execute()){
						// Statement failed
						return false;
					}
				}
				return true;
			}
			return false;
		}

		/**
		 * Gets the played cards for this game/hand
		 * @param  int $gameID The game to get the played cards for
		 * @return array The played cards, or false on error
		 */
		public function getPlayedCards($gameID){
			$sql = "SELECT 
					card.number AS number, card.suit AS suit,
					played.playedByID
					FROM playedcards AS played
					LEFT JOIN playingcards AS card ON played.playingcardID=card.id
					WHERE played.gameID=?
					ORDER BY played.cardOrder ASC
			";
									
			if($stmt = $this->mysqli->prepare($sql)){	
				//Bind parameter, execute, and bind result
				$stmt->bind_param("i", $gameID);
				$stmt->execute();
				$stmt->bind_result($number, $suit, $playedByID);
				
				$cardArr = array();
				while($stmt->fetch()){
					$card = array(
						"number" => $number,
						"suit" => $suit,
						"playedByID" => $playedByID
						);
					$cardArr[] = $card;
				}
				return $cardArr;
			}
			return false;
		}

		/**
		 * Mark a card in the database as being played.
		 * @param  int $gameID The game for which the card is being played
		 * @param  int $playedByID   The ID of the player who played the card.
		 * @param  array $card   "suit" and "number" index denoting the card in question
		 * @return boolean         Whether or not the play worked
		 */
		public function playCard($gameID, $playedByID, $card){
			if($card === null){
				$sql = "INSERT INTO playedcards (gameID, playedByID)
						VALUES (?, ?)";
			}else{
				$sql = "INSERT INTO playedcards (gameID, playingcardID, playedByID)
						VALUES (?, (
							SELECT id FROM playingcards WHERE suit=? AND number=?
							), ?)";
			}

			if($stmt = $this->mysqli->prepare($sql)){
				//Bind parameter and execute
				if($card === null){
					$stmt->bind_param("ii", $gameID, $playedByID);
				}else{
					$stmt->bind_param("isii", $gameID, $card["suit"], $card["number"], $playedByID);
				}
				return $stmt->execute();
			}
			return false;
		}

		/**
		 * Deletes from the database all played cards from this game
		 * @param  int $gameID The game for which to delete the cards from
		 * @return boolean         Whether or not the delete was successful
		 */
		public function clearPlayedCards($gameID){
			$sql = "DELETE FROM playedcards WHERE gameID=?";
									
			if($stmt = $this->mysqli->prepare($sql)){
				//Bind parameter and execute
				$stmt->bind_param("i", $gameID);
				return $stmt->execute();
			}
			return false;
		}

		/**
		 * Update the score of a player in a game. Called whenever a player scores any points.
		 * @param  int $gameID   The game for which to change the score
		 * @param  int $playerID The player for which to change the score
		 * @param  int $newScore The new score to put into the database, overwriting the old score
		 * @return boolean           Whether or not the operation was successful.
		 */
		public function updateScore($gameID, $playerID, $newScore){
			// Then add the new hand
			$sql = "UPDATE gamespaces
			    SET player1backPinPosition= CASE
			        WHEN player1ID=? THEN player1Score
			        ELSE player1backPinPosition
			    END,
			    player1Score = CASE
			        WHEN player1ID=? THEN ?
			        ELSE player1Score 
			    END,
			    player2backPinPosition = CASE
			        WHEN player2ID=? THEN player2Score
			        ELSE player2backPinPosition
			    END,
			    player2Score = CASE
			        WHEN player2ID=? THEN ?
			        ELSE player2Score
			    END
			    WHERE id=?
			";

			if($stmt = $this->mysqli->prepare($sql)){	
				$stmt->bind_param("iiiiiii", $playerID, $playerID, $newScore, $playerID, $playerID, $newScore, $gameID);
				return $stmt->execute();
			}else{
				return false;
			}
		}

		/**
		 * Switches whose turn it is in the database
		 * @param  int $gameID The game for which to change whose turn it is
		 * @return boolean         Whether or not it was successful
		 */
		public function switchTurn($gameID){
			// Then add the new hand
			$sql = "UPDATE gamespaces
			    SET turnID = CASE
			        WHEN turnID=player1ID THEN player2ID
			        WHEN turnID=player2ID THEN player1ID
			        ELSE turnID
			    END
			    WHERE id=?
			";

			if($stmt = $this->mysqli->prepare($sql)){	
				$stmt->bind_param("i", $gameID);
				return $stmt->execute();
			}else{
				return false;
			}
		}

		/**
		 * Switches whose turn it is in the database
		 * @param  int $gameID The game for which to change whose turn it is
		 * @return boolean         Whether or not it was successful
		 */
		public function switchDealer($gameID){
			// Then add the new hand
			$sql = "UPDATE gamespaces
			    SET dealerID = CASE
			        WHEN dealerID=player1ID THEN player2ID
			        WHEN dealerID=player2ID THEN player1ID
			        ELSE dealerID
			    END
			    WHERE id=?
			";

			if($stmt = $this->mysqli->prepare($sql)){	
				$stmt->bind_param("i", $gameID);
				return $stmt->execute();
			}else{
				return false;
			}
		}

	}
?>
