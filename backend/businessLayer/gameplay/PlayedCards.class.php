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
		/*
		Keep track of who played which card. Points for the last card played.
		 */
		
		/**
		 * The cards that have been player. Each card is an array with
		 * a PlayingCard and the ID of the player who played it
		 * array("card", "playedByID")
		 * @var array
		 */
		private $cards = array();

		public function __construct(){

		}

		/**
		 * Play a card.
		 * @param  PlayingCard $card The card to play
		 * @return int  The number of points from playing
		 */
		public function play($card){
			$count = $this->getCount();

		}

		public function getCardsOnTable(){

		}

		public function getAllCards(){

		}

		public function scoreForLastCard(){

		}

		public function getCount(){
			$runningCount = 0;
			for($i = 0; $i < count($this->cards); ++$i){
				if($runningCount+$this->cards[$i] > 31){
					$runningCount = 0;
				}

				$runningCount += $this->cards[$i];
			}

			return $runningCount;
		}

	}

?>