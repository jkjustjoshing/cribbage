<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/gameplay/PlayerHand.class.php");
	require_once(BACKEND_DIRECTORY . "/dataLayer/DataLayer.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/gameplay/CardDeck.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/gameplay/PlayedCards.class.php");

	/**
	 * Gamespace class
	 * @author Josh Kramer
	 * Models a board of cribbage
	 *
	 */
	class Gamespace{

		/**
		 * The gameID of ths gamespace
		 * @var int
		 */
		private $gameID;

		/**
		 * The IDs of the 2 players in this game
		 * @var int
		 */
		private $player1ID, $player2ID;

		/**
		 * The ID of the logged in player
		 * @var int
		 */
		private $playerID;

		/**
		 * The score for each player in this game (0-121)
		 * @var int
		 */
		private $player1Score, $player2Score;

		/**
		 * The back pin position for each player in this game (0-121)
		 * @var int
		 */
		private $player1backPinPosition, $player2backPinPosition;

		/**
		 * Either the deckID of the current game (will be an int type)
		 * or the deck object (will be an object). Test for data type
		 * to see which it is
		 * @var Either an int or a CardDeck object
		 */
		private $deck;

		/**
		 * The cut card on top of the deck for pegging
		 * @var PlayingCard
		 */
		public $cutCard;

		/**
		 * Either the cribID of the current game (will be an int type)
		 * or a PlayerHand object (will be an object). Test for data type
		 * to see which it is
		 * @var Either an int or a PlayerHand object
		 */
		private $crib;

		/**
		 * The ID of the player whose turn it currently is (only relevant
		 * if the game is currently in the pegging state).
		 * @var int
		 */
		public $turnID;

		/**
		 * The ID of the player whose deal it currently is.
		 * @var int
		 */
		public $dealerID;

		/**
		 * Game status regarding the game starting and ending:
		 * 		IN_PROGRESS
		 * 		FINISHED
		 * 		FOREFIT
		 * @var string
		 */
		public $gamestatus;

		/**
		 * Game state:
		 * 		DEALING
		 * 		CHOOSING_CRIB
		 * 		PEGGING
		 * 		VIEWING_HANDS
		 * 		WAITING_PLAYER_1
		 * 		WAITING_PLAYER_2
		 * @var string
		 */
		public $gamestate;

		/**
		 * Creates the framework in the database to start a new game.
		 * Should be called once a challenge between 2 players
		 * has been successfully negotiated. Player1 should always
		 * be dealer first.
		 * If a game between the two players already exists, simply 
		 * return that gameID
		 * @param  int $player1ID The ID of the first player, who should always be first dealer
		 * @param  int $player2ID The ID of the second player
		 * @return int The gameID of the new game
		 */
		public static function getGameID($player1ID, $player2ID){
			$database = DataLayer::getGameplayInstance();

			return $database->getGameID($player1ID, $player2ID);

		}

		public function __construct($gameID, $playerID){
			if(!is_numeric($gameID) || !is_numeric($playerID)){
				throw new Exception("Game ID '$gameID' or player ID '$playerID' aren't numbers.");
			}
			$this->gameID = intval($gameID);
			$this->playerID = intval($playerID);

			$database = DataLayer::getGameplayInstance();

			$gameInfo = $database->getGameInfo($gameID); // Gets the basic game info from the table;

			$this->player1ID = $gameInfo["player1ID"];
			$this->player2ID = $gameInfo["player2ID"];

			if($this->player1ID !== $playerID && $this->player2ID !== $playerID){
				// The playerID isn't part of this game - don't let them see it!
				throw new Exception("Player " . $playerID . " can't see a game between players " . $this->player1ID . " and " . $this->player2ID . ".");
			}

			$this->player1Score = $gameInfo["player1Score"];
			$this->player2Score = $gameInfo["player2Score"];
			$this->player1backPinPosition = $gameInfo["player1backPinPosition"];
			$this->player2backPinPosition = $gameInfo["player2backPinPosition"];
			$this->turnID = $gameInfo["turnID"];
			$this->dealerID = $gameInfo["dealerID"];
			$this->gamestatus = $gameInfo["gamestatus"];
			$this->gamestate = $gameInfo["gamestate"];
			$this->cutCard = $gameInfo["cutCard"];

		}

		/**
		 * Gets the scores of both players
		 * @return array An array of scores, indexed by the player's IDs
		 */
		public function getScores(){
			return array($this->player1ID => $this->player1Score,
				         $this->player2ID => $this->player2Score);
		}

		/**
		 * Add points to a player, both in the object and in the database
		 * @param  int $playerID The player for whom to add the points
		 * @param  int $pointsToAdd The points to add to the current score
		 * @return string     Error message, or empty string on success.
		 */
		private function updateScore($playerID, $pointsToAdd){
			if($playerID === $this->player1ID){
				$this->player1backPinPosition = $this->player1Score;
				$this->player1Score += $pointsToAdd;
				$score = $this->player1Score;
			}else if($playerID === $this->player2ID){
				$this->player2backPinPosition = $this->player2Score;
				$this->player2Score += $pointsToAdd;
				$score = $this->player2Score;
			}else{
				return "Can only change score of a player in the current game.";
			}

			$database = DataLayer::getGameplayInstance();

			if(!$database->updateScore($this->gameID, $playerID, $score)){
				return "There was a database error updating the score of player " . $playerID . ".";
			}

			return "";
		}

		/**
		 * Gets the back pin positions of both players
		 * @return array An array of back pin positions, indexed by the player's IDs
		 */
		public function getBackPinPositions(){
			return array($this->player1ID => $this->player1backPinPosition,
				         $this->player2ID => $this->player2backPinPosition);
		}



		public function getOpponentID(){
			return ($this->player1ID === $this->playerID ? $this->player2ID : $this->player1ID);
		}

		/**
		 * Shuffles the deck in the database a variable number of times.
		 * This method can only be called if the current player is also
		 * the dealer.
		 * @param  int $numberOfTimesToShuffle The number of times the deck is to be shuffled.
		 * @return  string Error message, or empty string on success
		 */
		public function shuffle($numberOfTimesToShuffle){
			if($this->dealerID !== $this->playerID){
				// Error - only the dealer can shuffle
				return "Only the dealer can shuffle the cards.";
			}
			// Get the deck object from the ID (if need be)
			if(!isset($this->deck)){
				$this->deck = CardDeck::getDeck($this->gameID);
			}

			// Shuffle the deck the number of times needed
			$this->deck->shuffle($numberOfTimesToShuffle);

			return "";

		}

		/**
		 * Returns a PlayerHand object for the current user 
		 * (or null if the dealer hasn't dealt yet)
		 * @return PlayerHand The current hand for the player.
		 */
		public function getOpponentHand(){
			$database = DataLayer::getGameplayInstance();
			if($this->player1ID === $this->playerID){
				$opponentID = $this->player2ID;
			}else{
				$opponentID = $this->player1ID;
			}


			$hands = $database->getHands($this->gameID);
			if(!isset($hands[$opponentID])){
				// No hand exists - dealer hasn't dealt yet.
				return null;
			}

			// Create the PlayerHand object to return
			$cards = array();
			foreach($hands[$opponentID] as $cardArr){
				//If we are at the right part of the game give the actual cards
				//Otherwise, display anonymous cards
				if($this->gamestate == "VIEWING_HANDS" ||
					$this->gamestate == "WAITING_PLAYER_1" ||
					$this->gamestate == "WAITING_PLAYER_2"){
					$cards[] = array("card"=>new PlayingCard($cardArr["number"], $cardArr["suit"]), "inHand"=>$cardArr["inHand"]);
				}else{
					$cards[] = array("card"=>new PlayingCard(0, null), "inHand"=>$cardArr["inHand"]);
				}
			}

			return new PlayerHand(PlayerHand::NOT_CRIB, $cards);

		}

		/**
		 * Gets the opponents PlayerHand object for the current
		 * user. Cards will be anonymous (so the player can see how
		 * many cards are in the hand) unless it's during the 
		 * counting of points state of the game.
		 * @return PlayerHand The current (possibly hidden) hand for the player's opponent
		 */
		public function getMyHand(){
			$database = DataLayer::getGameplayInstance();

			$hands = $database->getHands($this->gameID);
			if(!isset($hands[$this->playerID])){
				// No hand exists - dealer hasn't dealt yet.
				return null;
			}

			// Create the PlayerHand object to return
			$cards = array();
			foreach($hands[$this->playerID] as $cardArr){
				// If the gamestate is either VIEWING_HANDS or WAITING_PLAYER_# show opponent cards
				// Otherwise, show anonymous cards
				$cards[] = array("card"=>new PlayingCard($cardArr["number"], $cardArr["suit"]), "inHand"=>$cardArr["inHand"]);
			}

			return new PlayerHand(PlayerHand::NOT_CRIB, $cards);
		}

		/**
		 * Gets the crib's PlayerHand object for the current
		 * hand. Cards will be anonymous (so the player can see how
		 * many cards are in the hand) unless it's during the 
		 * counting of points state of the game.
		 * @return PlayerHand The current (possibly hidden) hand for the crib
		 */
		public function getCrib(){
			$database = DataLayer::getGameplayInstance();

			$hands = $database->getHands($this->gameID);
			if(!isset($hands["crib"])){
				// No hand exists - dealer hasn't dealt yet or no one has put anything into either crib.
				return null;
			}

			// Create the PlayerHand object to return
			$cards = array();
			foreach($hands["crib"] as $cardArr){
				// If the gamestate is either VIEWING_HANDS or WAITING_PLAYER_# show crib
				// Otherwise, show anonymous cards
				if($this->gamestate == "VIEWING_HANDS" ||
					$this->gamestate == "WAITING_PLAYER_1" ||
					$this->gamestate == "WAITING_PLAYER_2"){
					$cards[] = array("card"=>new PlayingCard($cardArr["number"], $cardArr["suit"]), "inHand"=>$cardArr["inHand"]);
				}else{
					$cards[] = array("card"=>new PlayingCard(0, null), "inHand"=>true);
				}
			}
			return new PlayerHand(PlayerHand::CRIB, $cards);
		}

		/**
		 * Deal the cards. Function only works if the
		 * player is dealer and the current gamestate
		 * is DEALING
		 * @param int $numberOfTimesToShuffle The number of times to shuffle the cards before dealing
		 * @return string Error message, or empty string on success
		 */
		public function deal($numberOfTimesToShuffle){
			// Check the user is dealer, and that the state is correct
			if($this->playerID !== $this->dealerID){
				return "You can only deal if you are the dealer.";
			}
			$database = Datalayer::getGameplayInstance();
			$gameInfo = $database->getGameInfo($this->gameID);
			if($gameInfo["gamestate"] !== "DEALING"){
				return "You can only deal when it's time to deal in the game.";
			}

			// Clear out the data from the game to make way for fresh stuff
			// don't need to clear hands - automatically happens when writing to them
			// clear the crib!
			// clear the PlayedCards object/table!

			// Grab the deck, reset it, and shuffle the correct number of times
			$deck = CardDeck::getDeck($this->gameID);
			$deck->resetDeck();
			$deck->shuffle($numberOfTimesToShuffle);

			// Pop cards from the deck and put, alternatingly, into PlayerHands.
			// Put 6 cards in each hand since that's the number you deal in cribbage
			$dealerHand = new PlayerHand(PlayerHand::NOT_CRIB);
			$opponentHand = new PlayerHand(PlayerHand::NOT_CRIB);
			for($i = 0; $i < 6; ++$i){
				//Put one card into the opponent's hand, then one into the dealer's
				$opponentHand->add($deck->pop());
				$dealerHand->add($deck->pop());
			}
			
			// Store the PlayerHands in the database
			$opponentID = ($this->dealerID == $this->player1ID ? $this->player2ID : $this->player1ID);
			$opponentHand->writeback($this->gameID, $opponentID);
			$dealerHand->writeback($this->gameID, $this->dealerID);


			// Change the state to CHOOSING_CRIB
			if($database->changeGameState($this->gameID, "CHOOSING_CRIB")){
				$this->gamestate = "CHOOSING_CRIB";
			}

			return "";

		}

		/**
		 * Put 2 cards in the crib.
		 * @param  PlayingCard $card1 The first card to put into the crib
		 * @param  PlayingCard $card2 The second card to put into the crib
		 * @return string Error message, or empty string on success.
		 */
		public function putCardsInCrib($card1, $card2){
			// Is the state correct and does the player have 6 cards in their hand
			if($this->gamestate !== "CHOOSING_CRIB"){
				return "You can only put cards in the crib when it's time to in the game.";
			}
			if($this->getMyHand()->numberOfCardsInHand() !== 6){
				return "You already put cards in the crib.";
			}

			$myHand = $this->getMyHand();

			if(count($myHand->getCards()) === 4){
				return "You already put 2 cards in the crib.";
			}

			// Are these two cards in the player's hand
			if(!$myHand->inHand($card1) || !$myHand->inHand($card2)){
				return "You can only put cards in the crib that are in your hand.";
			}

			// Put the cards in the crib, remove from players hand
			$myHand->remove($card1);
			$myHand->remove($card2);
			
			// Write the crib, write the removing them from the hand
			$myHand->writeback($this->gameID, $this->playerID);

			$database = DataLayer::getGameplayInstance();
			$cards = array(
					array("number" => $card1->getNumber(), "suit" => $card1->getSuit()),
					array("number" => $card2->getNumber(), "suit" => $card2->getSuit()),

				);
			if(!$database->putInCrib($this->gameID, $cards)){
				// Failed, put cards back in hand
				echo "pal bar";
				$myHand->add($card1);
				$myHand->add($card2);
				$myHand->writeback($this->gameID, $this->playerID);
				return "There was a database problem adding cards to the crib.";
			}
		
			// If the other player's hand has 4 cards change the state to CUTTING_CARD
			$crib = $this->getCrib();
			if(count($crib->getCards()) === 4){
				$database->changeGameState($this->gameID, "CUTTING_CARD");
			}
		}

		/**
		 * Choose the cut card, or get the cut card if $index is omitted.
		 * @param  int $index The index from where to get the cut card
		 * @return string Error message, or a PlayingCard object
		 */
		public function cutCard($index = null){
			$database = DataLayer::getGameplayInstance();
			if($index !== null){
				// Check user is not dealer and the state is correct
				if($this->playerID == $this->dealer){
					return "You can't cut the card if you are dealer.";
				}

				if($this->gamestate !== "CUTTING_CARD"){
					return "You can only cut the card when it's time to do so in the game.";
				}
				
				//Get the card at that index
				$deck = CardDeck::getDeck($this->gameID);
				$cutCard = $deck->pickCutCard($index);

				// Update the database
				$result = $database->setCutCard($this->gameID, array("suit"=>$cutCard->getSuit(), "number"=>$cutCard->getNumber()));
				if($result === false){
					return "There was a database error setting the cut card.";
				}else{
					
					// Change the state to PEGGING
					if($database->changeGameState($this->gameID, "PEGGING")){
						$this->gamestate = "PEGGING";
					}

					return $cutCard;
				}

			}else{
				//Get the cut card and return it
				$card = $database->getCutCard($this->gameID);
				if($card === false){
					return "There was a database problem getting the cut card.";
				}else{
					if(array_key_exists("suit", $card) && array_key_exists("number", $card)){
						return new PlayingCard($card["number"], $card["suit"]);
					}else{
						return new PlayingCard(0, null);
					}
				}
			}
		}

		/**
		 * Get the cards that have been played. It gets 
		 * all cards played this hand, even if they have been
		 * cleared after reaching 31.
		 * @return array Array of cards.
		 */
		public function getPlayedCards(){
			$playedCards = new PlayedCards($this->gameID);

			$cards = $playedCards->getAllCards();

			$toReturn = array();

			foreach($cards as $card){
				$toReturn[] = array("number"=>$card["card"]->getNumber(),
					                "suit"=>$card["card"]->getSuit(),
					                "playedByID"=>$card["playedByID"]);
			}

			return $toReturn;
		}


		/**
		 * Play a card.
		 * @param  PlayingCard $card The playing card to play, or null for a go
		 * @return string   Error message on error, or empty string on success.
		 */
		public function playCard($card){

			$database = DataLayer::getGameplayInstance();

			// Is it this player's turn and is the game state correct?
			if($this->playerID !== $this->turnID){
				return "It's not your turn.";
			}

			if($this->gamestate !== "PEGGING"){
				return "You can only play cards when it's time to do so in the game.";
			}

			$playedCards = new PlayedCards($this->gameID);

			// If this is a null card, make sure that no other card can be played
			if($card === null){

				$hand = $this->getMyHand();
				$cardArr = array();
				foreach($hand as $card){
					$cardArr[] = $card["card"];
				}

				$possible = $playedCards->test($cardArr);

				if($possible){
					return "One of the cards in your hand can be played. You must use that card before going.";
				}else{
					$playedCards->play(null, $this->playerID);

					// Switch turns
					$database->switchTurn($this->gameID);

					// If the last card that was played was mine, give me a point
					$result = $this->updateScore($this->playerID, 1);
					if($result !== ""){
						return $result;
					}
				}
			}else{
				// Make sure the card is in our hand
				if(!$this->getMyHand()->inHand($card)){
					return "You can only play a card that is in your hand.";
				}

				// Play the card
				$points = $playedCards->play($card, $this->playerID);

				if($points === false){
					return "You can't play that card because it makes the count go above 31";
				}else{

					// Add points to the user
					if($points !== 0){
						$result = $this->updateScore($this->playerID, $points);
						if($result !== ""){
							return $result;
						}
					}

					// Take card out of player's hand
					$myHand = $this->getMyHand();
					$myHand->peg($card);
					$myHand->writeback($this->gameID, $this->playerID);


					// Change whose turn it is
					$database->switchTurn($this->gameID);

					return "";
				}

			}

		}

		public function doneViewing(){
			// Set the state to either WAITING_FOR_PLAYER# or DEALING
		}


	}

?>