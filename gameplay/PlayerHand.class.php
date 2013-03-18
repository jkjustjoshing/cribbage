<?

	require_once("PlayingCard.class.php");

	/**
	 ** PlayerHand class
	 ** @author Josh Kramer
	 ** A hand of cards. Either a 6 card delt hand,
	 ** a hand of 4, or a crib
	 **/
	class PlayerHand{
		
		private $_cards = array();
		private $_score = 0;
		private $_scoreDirtyLastCut = null;
		public function __construct(){}

		public function add($playingCard){
			
			// Set the score as dirty
			$this->_scoreDirtyLastCut = null;

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

			// Set the score as dirty
			$this->_scoreDirtyLastCut = null;

			if(!is_object($playingCard)){
                throw new Exception("Tried adding a card to a PlayerHand that is not an object.");
            }

            if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
                throw new Exception("Tried adding a card to a PlayerHand that was not a card.");
            }

			foreach($this->_cards as $index=>$card){
				if($playingCard->equals($card)){
					//remove
					unset($this->_cards[$index]);
					return $card;
				}
			}
			return false;
		}

		public function totalPoints($cutCard){
			if($this->_scoreDirtyLastCut !== null && $cutCard->equals($this->_scoreDirtyLastCut)){
				// Score not dirty - don't recalculate it
				return $this->_score;
			}else{
				
				/* 
				 * TODO count points
				 *
				 * Calculate with a search of all the possible card
				 * combinations. For each combination check for all the cards
				 * being in a run, a pair (only, multiples will be counted by
				 * other combos), or all adding up to 15
				 */

				// Sort the cards first
				usort($this->_score

				$this->_score /* = $newScore */;
				return $this->_score;
			}
		}

		/**
		  * Finds all possible card combinations, and tallys all the
		  * points for each combination.
		  * Assume all cards are initially sorted
		 **/
		private function recursivePointsSearch($currentArray = array()){
			if(!is_array($currentArray)){
				throw new Exception('$currentArray must be an array');
			}

			if(count($currentArray) == 0){
				$accumulator = 0;
				foreach($this->_cards as $card){
					$accumulator += recursivePointsSearch(array($card));					
				}
				return $accumulator;
			}else{
				// Get card set's points, then recursively call method
				$pointAccumulator= 0;

				// If the cards are 2, check for a pair
				if(count($currentArray) == 2){
					$number = -1;
					foreach($currentArray as $pairCheckCard){
						if($number == -1){
							 $number = $pairCheckCard->getNumber();
						}else if ($pairCheckCard->getNumber() == $number){
							// Pair found!
							$pointsAccumulator += 2;
						}
					}
				}

				// Check for 15
				$fifteenAccumulator = 0;
				foreach($currentArray as $fifteenCheckCard){
					$fifteenAccumulator += $fifteenCheckCard->getCountValue();
				}
				if($fifteenAccumulator == 15){
					$pointsAccumulator += 2;
				}

				// Check for straight
				
				$indexOfLastCardInSet = max(array_keys($currentArray));
				
			}
			
			

			if($indexOfLastCardInSet == count($this->_cards)){
				//Last card is in set, stop recursing
			}

		}
	
		public function numberOfCardsInHand(){
			return count($this->_cards);
		}

		public getCards(){
			// Make sure we are passing out by value, not by reference
			$tempArr = $this->_cards;
			return $tempArr;
		}
		
		

	}


?>
