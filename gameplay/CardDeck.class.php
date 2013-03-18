<?php
	
	require_once("PlayingCard.class.php");

	class CardDeck{
		
		private $_cards = array();

		public function __construct($shuffle = false){
			
			// Assemble cards
			foreach(PlayingCard::getAllSuits() as $suit){
				for($number = 1; $number <= 13; ++$number){
					$this->_cards[] = new PlayingCard($number, $suit);
				}
			}

			if($shuffle){
				shuffle($this->_cards);
			}

		}



	}

?>
