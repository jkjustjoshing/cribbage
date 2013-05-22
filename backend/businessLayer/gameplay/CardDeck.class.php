<?php
	
	require_once(dirname(__FILE__) . "/PlayingCard.class.php");
	require_once(dirname(__FILE__) . "/../../dataLayer/DataLayer.class.php");

	/**
	 * CardDeck object
	 * Represents a card deck in the business logic layer
	 */
	class CardDeck{

		/**
		 * A constant to make more explicit what the parameter means
		 * when the constructor is called
		 */
		const SHUFFLE = true;
	
		/**
		 * An array of all the cards in the CardDeck
		 * @var array of PlayingCard objects
		 */
		private $cards = array();

		/**
		 * The database deckID of this deck. -1 if the
		 * deck is not in the database.
		 * @var integer
		 */
		private $gameID = -1;

		/**
		 * Is the deck in memory different from the deck in the database?
		 * If this value is true on destruct then write the deck back to 
		 * the database
		 * @var boolean
		 */
		private $isDirty = false;


		/**
		 * Gets an instance of a stored deck in the
		 * database, or a new one if it wasn't in the 
		 * database.
		 * @param  int $gameID The gameID of the deck to be returned
		 * @return CardDeck     The CardDeck object requested
		 */
		public static function getDeck($gameID, $shuffleNewDeck = false){
			$database = DataLayer::getGameplayInstance();

			$deck = $database->getCardDeck($gameID);

			if($deck === false){
				// Database problem, handle
			}

			if(count($deck) == 0){
				// Deck doesn't exist, return new deck
				$deck = new CardDeck($gameID, $shuffleNewDeck);
				return $deck;
			}


			$objectDeck = array();
			foreach($deck as $card){
				$objectDeck[] = new PlayingCard($card["number"], $card["suit"]);
			}

			$deck = new CardDeck($gameID, $objectDeck);

			return $deck;

		}

		/**
		 * Constructs a CardDeck. If an array of cards is passed
		 * the deck is created from those cards. If none are passed
		 * a new deck is created, either shuffled or not based
		 * on the $input variable
		 * Constructor is private, so the only way to instantiate
		 * a CardDeck is through the static getDeck() method.
		 * @param int  $gameID The gameID of the CardDeck
		 * @param [boolean or array] $input  Either an array of cards to initialize the object with, or whether or not to shuffle a new deck
		 */
		private function __construct($gameID, $input = false){
			
			if(is_array($input)){
				// Creating instance without generating new cards
				$this->cards = $input;
				$this->isDirty = false;
			}else{
				$this->isDirty = true;
				$shuffle = $input; // Whether or not to shuffle the deck
				// Assemble cards
				foreach(PlayingCard::getAllSuits() as $suit){
					for($number = 1; $number <= 13; ++$number){
						$this->cards[] = new PlayingCard($number, $suit);
					}
				}

				if($shuffle){
					// Shuffle is a native method
					// NOT $this->shuffle()!!!!!
					shuffle($this->cards);
				}
			}

			$this->gameID = $gameID;
		}


		/**
		 * When the CardDeck object gets destructed it needs to write back to the database
		 * itself if it is dirty.
		 */
		public function __destruct(){
			if($this->isDirty){
				$this->writeback();
			}

		}

		/**
		 * Shuffles a deck of cards
		 * @param  integer $numberOfTimesToShuffle How many times to shuffle the deck
		 */
		public function shuffle($numberOfTimesToShuffle = 1){
			if(count($this->cards) != 52){
				throw new Exception("Can't shuffle a deck that isn't full.");
			}

			for($i = 0; $i < $numberOfTimesToShuffle; ++$i){
				shuffle($this->cards);
			}

			$this->isDirty = true;

		}

		/**
		 * Get the top card from the deck and remove it from the deck.
		 * Use when dealing cards.
		 * @return PlayingCard The card that used to be on the top of the deck
		 */
		public function pop(){
			$this->isDirty = true;
			return array_pop($this->cards);
		}

		/**
		 * Remove a card from a specific index of the deck 
		 * and return that card (used for picking the cut card).
		 * @param  int $index The index at which to pick the card
		 * @return PlayingCard        The card that used to be at the given index of the deck
		 */
		public function pickCutCard($index){
			//print_r($this->cards);die();
			$card = $this->cards[$index];
			unset($this->cards[$index]);
			$this->cards = array_values($this->cards);
			
			$this->isDirty = true;

			return $card;
		}

		/**
		 * Get the size of the deck
		 * @return int The size of the deck
		 */
		public function size(){
			return count($this->cards);
		}

		/**
		 * Write the deck back to the database. If it formerly existed
		 * in the database clear the formed deck out first.
		 * @return string Error message string, which will be an empty string on success
		 */
		public function writeback(){
			if($this->isDirty == false){
				// The deck isn't dirty, no need to writeback
				return;
			}

			$cardArray = array();
			foreach($this->cards as $card){
				$cardArray[] = array(
					"number" => $card->getNumber(),
					"suit" => $card->getSuit()
					);
			}

			$database = DataLayer::getGameplayInstance();

			$result1 = $database->deleteCardDeck($this->gameID);
			if($result1 === false){
			}
			$result2 = $database->insertCardDeck($this->gameID, $cardArray);

			$errorString = "";
			if($result1 === false){
				// TODO deal with error
				$errorString .= "Deleting card deck failed on " . __LINE__ . " file " . __FILE__ ."\n";
			}else if($result2 === false){
				// TODO deal with error
				$errorString .= "Writing the card deck failed on " . __LINE__ . " file " . __FILE__ . "\n";
			}else{
				//Success
				$this->isDirty = false;
			}

			return $errorString;

		}

		/**
		 * Resets the deck with 52 cards and shuffled. This should be called
		 * before each new hand to put all 52 cards back into the deck.
		 */
		public function resetDeck(){

			$this->cards = array();

			foreach(PlayingCard::getAllSuits() as $suit){
				for($number = 1; $number <= 13; ++$number){
					$this->cards[] = new PlayingCard($number, $suit);
				}
			}

			// Shuffle the deck once
			shuffle($this->cards);

			$this->isDirty = true;
		}


		/**
		 * Used for testing, shouldn't ever call this in production.
		 * @return array The array of card objects in the deck
		 */
		public function getCards($testString = ""){
			if($testString == "This is part of a unit test."){
				return $this->cards;
			}else{
				throw new Exception("This method should not be called outside a unit test.");
			}
		}

	}

?>