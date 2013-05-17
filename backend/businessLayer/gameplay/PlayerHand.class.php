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
		 * Is this object different from what the database is holding?
		 * @var boolean Whether or not this object is different from what the database is holding.
		 */
		private $isDirty = false;
		
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
					}else if(get_class($card) != get_class(new PlayingCard(1, "club"))){
						// Not a card object
						throw new InvalidArgumentException("Initializing with a non-card object");
					}

					$this->_cards[] = array("card" => $card, "inHand" => true);
					$this->isDirty = true;
				}
			}catch(InvalidArgumentException $e){
				// Do this to remove any cards that could have been added before the non-card was caught
				$this->_cards = array();
				throw $e;
			}
		}

		
		public function writeback($gameID, $playerID){
			if($this->isDirty){
				$cards = array();

				foreach($this->_cards as $card){
					$cards[] = array(
							"suit" => $card["card"]->getSuit(),
							"number" => $card["card"]->getNumber(),
							"inHand" => $card["inHand"]
						);
				}

				$database = DataLayer::getGameplayInstance();
				return $database->writePlayerHand($gameID, $playerID, $cards);
			}
		}
		
		/**
		 * Choose a card to mark as played for pegging. Doesn't 
		 * remove the card from the hand for scoring, just for
		 * availability for using to peg again.
		 * @param  PlayingCard $playingCard The playing card to mark as played (must already be in hand)
		 */
		public function peg($playingCard){
			$tempDirty = $this->isDirty;
			$this->isDirty = true;

			if(!is_object($playingCard)){
                throw new InvalidArgumentException("Tried playing a card from a PlayerHand that is not an object.");
            }

            if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
                throw new InvalidArgumentException("Tried playing a card from a PlayerHand that was not a card.");
            }

			foreach($this->_cards as $index=>$card){
				if($playingCard->equals($card["card"])){
					//play
					$this->_cards[$index]["inHand"] = false;
					return $card["card"];
				}
			}
			
			// If the object wasn't changed restore the original dirty value
			$this->isDirty = $tempDirty;

			throw new UnexpectedValueException("Card " . $playingCard->__toString() . " not in hand");
		}

		/**
		 * Adds a playing card to the hand.
		 * Throws an exception if a non-card is passed
		 * @param A PlayingCard instance
		 * @ throws InvalidArgumentException if not a PlayingCard instance
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
			$this->_cards[] = array("card" => $playingCard, "inHand" => true);

			$this->isDirty = true;
		}

		/**
		 * Remove a specified playing card from the hand.
		 * If the card isn't in the hand or is a non-card, exception thrown
		 * @return The card that was removed
		 * @throws InvalidArgumentException if passed a non-card value
		 * @throws UnexpectedValueException if passed a card not in the hand
		 */
		public function remove($playingCard){
			$tempDirty = $this->isDirty;
			$this->isDirty = true;

			// Set the score as dirty
			$this->_scoreDirtyLastCut = null;

			if(!is_object($playingCard)){
                throw new InvalidArgumentException("Tried removing a card from a PlayerHand that is not an object.");
            }

            if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
                throw new InvalidArgumentException("Tried removing a card from a PlayerHand that was not a card.");
            }

			foreach($this->_cards as $index=>$card){
				if($playingCard->equals($card["card"])){
					//remove
					unset($this->_cards[$index]);
					return $card;
				}
			}
			
			// If the object wasn't changed restore the original dirty value
			$this->isDirty = $tempDirty;

			throw new UnexpectedValueException("Card " . $playingCard->__toString() . " not in hand");
		}

		/**
		 * Test if the playing card given is in the hand currently
		 * @param  PlayingCard $playingCard The playing card in question
		 * @return boolean     Whether or not the card is in the hand.
		 */
		public function inHand($playingCard){
			if(!is_object($playingCard)){
                return false;
            }

			if(get_class($playingCard) != get_class(new PlayingCard(1, "club"))){
				return false;
			}

			foreach($this->_cards as $index=>$card){
				if($playingCard->equals($card["card"])){
					return true;
				}
			}
			
			return false;
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
				$firstCard = reset($this->_cards);
				$suit = $firstCard["card"]->getSuit(); // Get suit of first item (even if first index isn't predictable)
				$flush = true;
				foreach($this->_cards as $card){
					if($card["card"]->getSuit() != $suit){
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
					if($card["card"]->getSuit() == $cutCard->getSuit() && $card["card"]->getNumber() == 11){
						$this->_score += 1;
						break;
					}
				}



if($debug)echo "<pre>entering points search\n";	
				$cardsPlusCut = array();
				foreach($this->_cards as $card){
					$cardsPlusCut[] = $card["card"];
				}
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

				// Add any fifteen points
				$this->_score += $this->recursiveFifteen($cardsPlusCut, $debug);

if($debug)				echo "</pre>";
				return $this->_score;
			}
		}

		/**
		  * Finds all possible card combinations, and tallys all the
		  * points for each combination.
		  * Assume all cards are initially sorted
		 **/
		private function recursiveFifteen($currentArray = array(), $debug = false){
			// Check for 15
			$fifteenAccumulator = 0;
			foreach($currentArray as $fifteenCheckCard){
				$fifteenAccumulator += $fifteenCheckCard->getCountValue();
			}
			if($fifteenAccumulator == 15) return 2;

			if(count($currentArray) == 2) return 0; // End of points counting, no 15 found
	
			// Remove each possible card and recursively call
			$pointAccumulator = 0;
			for($i = 0; $i < 5; ++$i){
				if(!isset($currentArray[$i])){
					break; // So we have no duplicate card sets
				}
				$tempArray = $currentArray;
				unset($tempArray[$i]);
				$pointAccumulator += $this->recursiveFifteen($tempArray, $debug);
			}
			return $pointAccumulator;
		}
	
		/**
		 * Gets the current number of cards in the hand.
		 * @return The number of cards in the hand
		 */
		public function numberOfCardsInHand(){
			$count = 0;
			foreach($this->_cards as $card){
				if($card["inHand"]){
					++$count;
				}
			}
			return $count;
		}

		/**
		 * Return an array of all the cards in the hand
		 * @return Array Array of cards and inHand values
		 */
		public function getCards(){
			return $this->_cards;
		}
		
		/**
		 * Returns an array of cards in the hand in array form to be sent to the client
		 * @return array A plain array of data representing cards
		 */
		public function cardArray(){
			$arr = array();

			foreach($this->_cards as $card){
				$cardArr = array(
						"suit" => $card["card"]->getSuit(),
						"number" => $card["card"]->getNumber(),
						"inHand" => $card["inHand"]
					);
				$arr[] = $cardArr;
			}

			return $arr;
		}
		

	}


?>