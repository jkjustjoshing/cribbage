<?php
	
	require_once("PlayingCard.class.php");

	class CardDeck{

		const SHUFFLE = true;
				
		private $_cards = array();

		private $_cutCard;

		public function __construct($shuffle = false){
			
			// Assemble cards
			foreach(PlayingCard::getAllSuits() as $suit){
				for($number = 1; $number <= 13; ++$number){
					$this->_cards[] = new PlayingCard($number, $suit);
				}
			}

			if($shuffle){
				// Shuffle is a native method
				shuffle($this->_cards);
			}

		}

		public function shuffle(){
			if(count($this->_cards) != 52){
				throw Exception("Can't shuffle a deck that isn't full.");
			}
			shuffle($this->_cards);
		}

		public function pop(){
			return array_pop($this->_cards);
		}

		public function pickCutCard($index){
			$card = $this->_cards[$index];
			unset($this->_cards[$index]);
			$this->_cards = array_values($this->_cards);
			
			return $card;
		}

		public function size(){
			return count($this->_cards);
		}

	}

?>