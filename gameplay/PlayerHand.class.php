<?

	require_once("PlayingCard.class.php");

	/**
	 * PlayerHand class
	 * @author Josh Kramer
	 * A hand of cards. Either a 6 card delt hand,
	 * a hand of 4, or a crib
	 */
	class PlayerHand{
		
		/**
		 * An array of the cards stored in the hand
		 */	
		private $_cards = array();

		/**
		 * The score of the hand, and whether or not the score is "dirty".
		 * This means that if the object is queried for the score twice without
		 * the hand changing it can use a cached value without recomputing
		 * it. When the score is not dirty the cut card used for the computation
		 * is stored, since that changing will change the value of the hand.
		 */
		private $_score = 0;
		private $_scoreDirtyLastCut = null;
		
		/**
		 * Stores if this hand is a crib or not. The only difference
		 * is how flushes are calculated
		 */
		private $_isCrib = false;
		
		/**
		 * Constructor for the hand. Can optionally pass an array of cards that will
		 * be put into the hand.
		 * @throws InvalidArgumentException If any items in the passed array are not card objects
		 */
		public function __construct($cardArray = array()){
			try{
				foreach($cardArray as $card){
					if(!is_object($card)){
						throw new InvalidArgumentException("Initializing with a non-object card");
					}else if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
						// Not a card object
						throw new InvalidArgumentException("Initializing with a non-card object");
					}

					$this->_cards[] = $tard;
				}
			}catch(InvalidArgumentException $e){
				$this->_cards = array();
				throw $e;
			}
		}

		/**
		 * Adds a playing card to the hand.
		 * Throws an exception if a non-card is passed
		 * @param A PlayingCard instance
		 * @throws InvalidArgumentException if not a PlayingCard instance
		 */
		public function add($playingCard){
			
			// Set the score as dirty
			$this->_scoreDirtyLastCut = null;

			if(!is_object($playingCard)){
				throw new InvalidArgumentException("Tried adding a card to a PlayerHand that is not an object.");
			}

			if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
				throw new InvalidArgumentException("Tried adding a card to a PlayerHand that was not a card.");
			}

			// Confirmed that $playingCard is a card
			$this->_cards[] = $playingCard;
		}

		/**
		 * Remove a specified playing card from the hand.
		 * If the card isn't in the hand or is a non-card, exception thrown
		 * @return The card that was removed
		 * @throws InvalidArgumentException if passed a non-card value
		 * @throws UnexpectedValueException if passed a card not in the hand
		 */
		public function remove($playingCard){

			// Set the score as dirty
			$this->_scoreDirtyLastCut = null;

			if(!is_object($playingCard)){
                throw new InvalidArgumentException("Tried adding a card to a PlayerHand that is not an object.");
            }

            if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
                throw new InvalidArgumentException("Tried adding a card to a PlayerHand that was not a card.");
            }

			foreach($this->_cards as $index=>$card){
				if($playingCard->equals($card)){
					//remove
					unset($this->_cards[$index]);
					return $card;
				}
			}
			
			throw new UnexpectedValueException("Card " . $playingCard->__toString() . " not in hand");
		}

		/**
		 * Calculates the total number of points in a hand, combining in a given
		 * cut card.
		 * @return The number of points in the hand with the given cut card
		 */
		public function totalPoints($cutCard){
			if($this->numberOfCardsInHand() < 4){
				// Score can't be calculated with a smaller-than-full hand
				return 0;
			}
			
			if($this->_scoreDirtyLastCut !== null && $cutCard->equals($this->_scoreDirtyLastCut)){
				// Score not dirty - don't recalculate it
				return $this->_score;
			}else{
				$this->_score = 0;
				/* 
				 * TODO count points
				 *
				 * Calculate with a search of all the possible card
				 * combinations. For each combination check for all the cards
				 * being in a run, a pair (only, multiples will be counted by
				 * other combos), or all adding up to 15
				 */

				// Look for flush
				$suit = $this->_cards[0]->getSuit();
				$flush = true;
				foreach($this->_cards as $card){
					if($card->getSuit() != $suit){
						$flush = false;
						break;
					}
				}
				if($flush){
					if($cutCard->getSuit() == $suit){
						$this->_score += 5;
					}else if(!$this->_isCrib){
						$this->_score += 4;
					}
				}

				// Look for knobbs
				foreach($this->_cards as $card){
					if($card->getSuit() == $cutCard->getSuit() && $card->getNumber() == 11){
						$this->_score += 1;
						break;
					}
				}


				// Sort the cards first
				//usort($this->_score

				//$this->_score = $this->recursivePointsSearch(;
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
	
		/**
		 * Gets the current number of cards in the hand.
		 * @return The number of cards in the hand
		 */
		public function numberOfCardsInHand(){
			return count($this->_cards);
		}

		/**
		 * Return an array of all the cards in the hand
		 * @return All the cards in the hand
		 */
		public function getCards(){
			// Make sure we are passing out by value, not by reference
			$tempArr = $this->_cards;
			return $tempArr;
		}
		
		

	}


?>
