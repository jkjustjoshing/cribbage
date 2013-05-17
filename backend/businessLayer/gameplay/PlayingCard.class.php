<?

	/**
	 * PlayingCard class
	 * @author Josh Kramer
	 * Models a playing card
	 *
	 */
	class PlayingCard{
		
		/**
		 * The suit and number of the card
		 */
		private $_suit, $_number;
		
		/**
		 * All possible suits
		 */
		private static $suits = array(
			"diamond",
			"club",
			"heart",
			"spade"
		);

		/**
		 * Constructs a playing card
		 * @param $number A string or integer of the number of the card (or "J"/"Q"/"K")
		 * @param $suit A string of the suit
		 * @throws Exception if the paramaters are out of bounds or invalid
		 */
		public function __construct($number, $suit){
			if(in_array($suit, self::$suits)){
				$this->_suit = $suit;
			}else if($suit === null){
				// Anonymous card being created
				$this->_suit = $suit;
			}else{
				throw new Exception("Card can't be created with suit " . $suit . ".");
			}

			if(is_int($number) && $number >= 0 && $number < 14){
				$this->_number = $number;
			}else if(is_string($number)){
					if(("".intval($number)) == $number && intval($number) > 0 && intval($number < 14)){
							$this->_number = intval($number);
					}else{
						switch($number){
							case "J":
								$this->_number = 11;
								break;
							case "Q":
								$this->_number = 12;
								break;
							case "K":
								$this->_number = 13;
								break;
							default:
								throw new Exception("Card can't be created with a number " . $number . ".");
						}
					}
			}else{
				throw new Exception("Card can't be created with a non-numeric value");
			}
		} // end __construct

		/**
		 * Gets the suit of the card
		 * @return The suit of the card
		 */
		public function getSuit(){
			return $this->_suit;
		}
		
		/**
		 * Gets the number of the card
		 * @return The number of the card
		 */
		public function getNumber(){
			return $this->_number;
		}

		/**
		 * Gets the count of the card. Returns the
		 * same value as getNumber() for 1-10, but for
		 * J, Q, K they also return 10.
		 * @return The count value of the card for counting to 15
		 */
		public function getCountValue(){
			if($this->_number > 10){
				return 10;
			}else{
				return $this->_number;
			}
		}

		/**
		 * Tests the card's equality to another card
		 * @return if the cards are equal or not
		 */
		public function equals($other){
			if(!is_object($other)){
				return false;
			}

			if(get_class() != get_class($other)){
				return false;
			}

			if($this->_number === $other->_number && $this->_suit === $other->_suit){
				return true;
			}else{
				return false;
			}
		}

		/**
		 * Gets a list of possible suits
		 * @return An array of possible suit values
		 */
		public static function getAllSuits(){
			return self::$suits;
		}

		/**
		 * toString method returns a string with the type of 
		 * card. Does not return HTML or SVG. 
		 * @return A string to identify the card
		 */
		public function __toString(){
			if($this->_suit === null){
				return "Hidden card";
			}

			$number = $this->_number;
			if($number == 11) $number = "J";
			else if($number == 12) $number = "Q";
			else if($number == 13) $number = "K";

			return $number . " of " . $this->_suit . "s";
		}

	}

?>