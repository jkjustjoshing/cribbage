<?

	/**
	 ** PlayingCard class
	 ** @author Josh Kramer
	 ** Models a playing card
	 **
	 **/
	class PlayingCard{
		
		private $_suit, $_number;
		const SUITS = array(
			"diamond",
			"club",
			"heart",
			"diamond"
		);

		function __construct($suit, $number){
			if(in_array($suit, $suits){
				$_suit = $suit;
			}else{
				throw new Exception("Card can't be created with suit " . $suit . ".");
			}

			if(is_int($number) && $number > 0 && $number < 14){
				$_number = $number;
			}else if(is_string($number)){
					if(intval($number) > 0 && intval($number < 14)){
							$_number = intval($number);
					}else{
						switch($number){
							case "J":
								$_number = 11;
								break;
							case "Q":
								$_number = 12;
								break;
							case "K":
								$_number = 13;
								break;
							default:
								throw new Exception("Card can't be created with a number " . $number . ".");
						}
					}
			}else{
				throw new Exception("Card can't be created with a non-numeric value");
			}
		} // end __construct



	}

?>
