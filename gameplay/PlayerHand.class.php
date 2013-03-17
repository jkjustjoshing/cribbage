<?

	/**
	 ** PlayerHand class
	 ** @author Josh Kramer
	 ** A hand of cards. Either a 6 card delt hand,
	 ** a hand of 4, or a crib
	 **/
	class PlayerHand{
		
		private $_cards = array();
		private $
		public function __construct(){}

		public function add($playingCard){
			if(!is_object($playingCard)){
				throw new Exception("Tried adding a card to a PlayerHand that is not an object.");
			}

			if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
				throw new Exception("Tried adding a card to a PlayerHand that was not a card.");
			}

			// Confirmed that $playingCard is a card
			$_cards[] = $playingCard;
		}

		public function remove($playingCard){
			if(!is_object($playingCard)){
                throw new Exception("Tried adding a card to a PlayerHand that is not an object.");
            }

            if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
                throw new Exception("Tried adding a card to a PlayerHand that was not a card.");
            }

			foreach($index=>$this->_cards as $card){
				if($playingCard->equals($card)){
					//remove
					unset($this->_cards[$index]);
					return $card;
				}
			}
			return false;
		}

		public function totalPoints(){
			//count points
		}

		public function numberOfCardsInHand(){
			return count($this->_cards);
		}
		
		

	}


?>
