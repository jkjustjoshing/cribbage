<?

	/**
	 ** PlayingCard class
	 ** @author Josh Kramer
	 ** Models a playing card
	 **
	 **/
	class PlayingCard{
		
		private $_suit, $_number;
		private static $suits = array(
			"diamond",
			"club",
			"heart",
			"spade"
		);

		public function __construct($number, $suit){
			if(in_array($suit, self::$suits)){
				$this->_suit = $suit;
			}else{
				throw new Exception("Card can't be created with suit " . $suit . ".");
			}

			if(is_int($number) && $number > 0 && $number < 14){
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

		public static function getSuit(){
			return $this->_suit;
		}

		public static function getNumber(){
			return $this->_number;
		}

		public static function toString(){
			return $this->_number . " of " . $this->_suit;
		}

		public static function toSVG(){
			//TODO
			return "";
		}

	}

?>
