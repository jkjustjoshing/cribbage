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
		 * Used to tell if this hand is a crib or not
		 */
		const CRIB = 1;
		const NOT_CRIB = 0;

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
		private $_isCrib;
		
		/**
		 * Constructor for the hand. Can optionally pass an array of cards that will
		 * be put into the hand.
		 * @throws InvalidArgumentException If any items in the passed array are not card objects
		 */
		public function __construct($isCrib = self::NOT_CRIB, $cardArray = array()){
			$this->_isCrib = $isCrib;
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
		public function totalPoints($cutCard, $debug = false){
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
				$suit = reset($this->_cards)->getSuit(); // Get suit of first item (even if first index isn't predictable)
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
					}else if(!$this->_isCrib == self::CRIB){
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



if($debug)echo "<pre>entering points search\n";	
				$cardsPlusCut = $this->_cards;
				$cardsPlusCut[] = $cutCard;

				usort($cardsPlusCut, function($a, $b){
                    if(!is_object($a) || !is_object($b)){
                        throw new Exception("Comparing non-objects");
                    }

                    if($a->getNumber() == $b->getNumber()) return 0;
                    return ($a->getNumber() < $b->getNumber() ? -1 : 1);
                });
                $cardsPlusCut = array_values($cardsPlusCut);

				// Look for pairs and straights
				$runLength = 0;
                $multiplier = 1;
                $multiplierForThisNumber = 1;
                $thisNumberForMultiplier = 0;
				for($i = 0; $i < count($cardsPlusCut); ++$i){

					// Look for pairs
					for($j = $i+1; $j < count($cardsPlusCut); ++$j){
						if($cardsPlusCut[$i]->getNumber() == $cardsPlusCut[$j]->getNumber()){
							$this->_score += 2;
						}
					}
					
					// Look for straights
if($debug)					echo "______________\n";
if($debug)					echo "straight search card $i -> $cardsPlusCut[$i]\n";

					if($runLength == 0){
if($debug)						echo "run length 0";
						$runLength = 1;
						$thisNumberForMultiplier = $cardsPlusCut[$i]->getNumber();
					}else{
						if($thisNumberForMultiplier == $cardsPlusCut[$i]->getNumber()){
							$multiplierForThisNumber++;
						}else if($thisNumberForMultiplier + 1 == $cardsPlusCut[$i]->getNumber()){
							$runLength++;
							$multiplier *= $multiplierForThisNumber;
							$multiplierForThisNumber = 1;
							$thisNumberForMultiplier++;
						}else{
							$multiplier *= $multiplierForThisNumber;
							if($runLength >= 3){
								$this->_score += $runLength * $multiplier;
							}
							$thisNumberForMultiplier = $cardsPlusCut[$i]->getNumber();
							$multiplierForThisNumber = 1;
							$multiplier = 1;
							$runLength = 1;
						}
					}
if($debug)				echo "multiplier - $multiplier\n";
if($debug)				echo "multiplierForThisNumber - $multiplierForThisNumber\n";
if($debug)				echo "runLength - $runLength\n";
if($debug)				echo "thisNumberForMultiplier - $thisNumberForMultiplier\n";
						
				}
				if($runLength >= 3){
					$this->_score += $runLength * $multiplier * $multiplierForThisNumber;
				}

				

				$this->_score += $this->recursivePointsSearch($cardsPlusCut, $debug);
if($debug)		echo "</pre>";
				return $this->_score;
			}
		}

		/**
		  * Finds all possible card combinations, and tallys all the
		  * points for each combination.
		  * Assume all cards are initially sorted
		 **/
		private function recursivePointsSearch($currentArray = array(), $debug = false){
if($debug)			echo "called recursivePointsSearch\n";
			if(!is_array($currentArray)){
				throw new Exception('$currentArray must be an array');
			}
			
			$pointAccumulator = 0;
			// If we are just calling this, reset the keys to the array and order
if($debug){//          print_r($currentArray);
	echo "-------------------";
	foreach($currentArray as $card){
		echo $card->getNumber() . ", ";
	}
	echo "\n";
	
}
			// If the cards are 2, check for a pair
/*			if(count($currentArray) == 2){
				$number = -1;
				foreach($currentArray as $pairCheckCard){
					if($number == -1){
						 $number = $pairCheckCard->getNumber();
					}else if($pairCheckCard->getNumber() == $number){
						// Pair found!
						$pointAccumulator += 2;
					}
				}
			}
*/
			// Check for 15
			$fifteenAccumulator = 0;
			foreach($currentArray as $fifteenCheckCard){
				$fifteenAccumulator += $fifteenCheckCard->getCountValue();
			}
			if($fifteenAccumulator == 15){
				$pointAccumulator += 2;
			}
if($debug) echo "points post 15 - $pointAccumulator\n";
/*			// Check for straight
			if(count($currentArray) >= 3){
				$run = true;
				$firstCard = array_slice($currentArray, 0, 1);
				$lastNumber = $firstCard[0]->getNumber() - 1;
				foreach($currentArray as $card){
					if($lastNumber+1 != $card->getNumber()){
						$run = false;
						break;
					}else{
						++$lastNumber;
					}
				}
				if($run){
					if(count($currentArray) == 3){
						$pointAccumulator += 3;
					}else if(count($currentArray) == 4){
						$pointAccumulator -= 2; // Already 6 points from the 2x3 card run but should only be 4 total 
					}else if(count($currentArray) == 5){
						//Already at 5, shouldn't change
					}
				}
			}	
*/
if($debug) echo "points after run - $pointAccumulator\n";
			if(count($currentArray) == 2){
if($debug)					echo "2 left\n";
				// End of points counting, return accumulator
				return $pointAccumulator;
			}else{
if($debug)				echo "recurse...\n";
				// Remove each possible card and recursively call
				for($i = 0; $i < 5; ++$i){
					if(!isset($currentArray[$i])){
						break; // So we have no duplicate card sets
					}
					$tempArray = $currentArray;
					unset($tempArray[$i]);
					$pointAccumulator += $this->recursivePointsSearch($tempArray, $debug);
				}
				return $pointAccumulator;
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
