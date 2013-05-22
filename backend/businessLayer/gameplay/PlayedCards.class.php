<?

	require_once(dirname(__FILE__) . "/PlayingCard.class.php");
	require_once(dirname(__FILE__) . "/../../dataLayer/DataLayer.class.php");

	/**
	 * PlayedCards class
	 * @author Josh Kramer
	 * Holds all cards that have been played/pegged
	 *
	 */
	class PlayedCards{

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/*
	Process for playing a card
		Check to see if the card can be played without clearing the count
		if it can not be
			if the last card played was the same player as currently is trying to play
				the other player already "go"ed - get a point, clear the crib and get a point
				pass the turn
			else
				"go-ing"
				pass the turn
		else
			play the card
			update the count
			get points
			pass the turn

	 */
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		
		private $cards;
		private $screenCards;
		
		/**
		 * The cards that have been player. Each card is an array with
		 * a PlayingCard and the ID of the player who played it
		 * array("card", "playedByID")
		 * @var array
		 */
		private $cards = array();

		public function __construct(){
			// Put cards into cards and screenCards
		}

		/**
		 * Play a card.
		 * @param  PlayingCard $card The card to play
		 * @return int  The number of points from playing, or false if te card can't be played
		 */
		public function play($card){
			if($this->getCount() + $card->getCountValue() > 31){
				return false;
			}

			$points = $this->scoreIfAddingCard($card);
			
		}

		/**
		 * Returns the same as the play() method, but without changing anything.
		 * Call this method before calling test to make sure that a card can 
		 * actually be played. Can also pass a card array to test them all
		 * @param  PlayingCard/array $cards Either a PlayingCard object or an array of them to test
		 * @return boolean/int   Returns the number of points playing a card will earn by being
		 *                       played, or false if the card can't be played. If an array is passed
		 *                       a boolean will be returned indicating if any of the cards is 
		 *                       playable.
		 */
		public function test($cards){
			if(!is_array($cards)){
				return $this->scoreIfAddingCard($cards);
			}else{

				// If ANY of the cards can be played, return true
				foreach ($cards as $card){
					if($this->getCount() + $card->getCountValue() <= 31){
						return true;
					}
				}

				// None of the cards could be played
				return false;

			}
		}

		public function getScreenCards(){
			return $this->screenCards;
		}

		public function getAllCards(){
			return $this->cards;
		}

		/**
		 * Gets the score that playing the given card would earn
		 * @param  PlayingCard $card The card to test playing
		 * @return int      The number of points earned, or false on failure
		 */
		public function scoreIfAddingCard($card){
			if($this->getCount() + $card->getCountValue() > 31){
				return false;
			}

			$score = 0;

			// Test for 15 and 31
			$updatedCount = $this->getCount() + $card->getCountValue();
			if($updatedCount == 15 || $updatedCount == 31){
				$score += 2;
			}
			
			// Test for pair/3/4 of a kind
			$countOfSameCards = 1;
			for($i = count($this->screenCards)-1; $i > 0; --$i){
				if($this->screenCards[$i]->getNumber() === $card->getNumber()){
					$countOfSameCards++;
				}else{
					break;
				}
			}
			if($countOfSameCards == 2){
				$score += 2;
			}else if($countOfSameCards == 3){
				$score += 6;
			}else if($countOfSameCards == 4){
				$score += 12;
			}

			// Test for straight
			// Take the last 3 cards, sort, and check if in order. If so check 4, and on
			if(count($this->screenCards) > 1){
				$maxStraight = 0;
				// For each length of straight possible
				for($numToLookBack = 2; $numToLookBack < count($this->screenCards); ++$numToLookBack){
					
					//Accumulate the last X cards into an array to check
					$cardArr = array($card);
					for($add = 0; $add < $numToLookBack; ++$add){
						$cardArr[] = $this->screenCards[count($this->screenCards)-1-$add];
					}

					// Sort the array of last cards
					usort($cardArr, function($a, $b){
	                    if(!is_object($a) || !is_object($b)){
	                        throw new Exception("Comparing non-objects");
	                    }

	                    if($a->getNumber() == $b->getNumber()) return 0;
	                    return ($a->getNumber() < $b->getNumber() ? -1 : 1);
	                });
	                $cardArr = array_values($cardArr);

	                // Check for straight
	                for($i = 1; $i < count($cardArr); ++$i){
	                	if($cardArr[$i-1]->getNumber() + 1 !== $cardArr[$i]->getNumber()){
	                		break 2;
	                	}
	                }

	                if($maxStraight < $numToLookBack + 1){
	                	$maxStraight = $numToLookBack + 1;
	                }
				}

				$score += $maxStraight;
			}

			return $score;
		}

		public function getCount(){
			$runningCount = 0;
			for($i = 0; $i < count($this->screenCards); ++$i){
				$runningCount += $this->screenCards[$i];
			}

			return $runningCount;
		}

	}
?>