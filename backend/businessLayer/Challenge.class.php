<?

	require_once(BACKEND_DIRECTORY . "/dataLayer/DataLayer.class.php");

	/**
	 * Challenge class
	 * @author Josh Kramer
	 * Models a challenge between 2 players
	 *
	 */
	class Challenge{
		
		public $id = null;
		public $challengerID = null;
		public $challengeeID = null;
		public $status = null;
		
		public function __construct($id, $challengerID, $challengeeID, $status){
			$this->id = $id;
			$this->challengerID = $challengerID;
			$this->challengeeID = $challengerID;
			$this->status = $status;
		}
		
		
		/**
		 * getChallenges($playerID, $challenger = null)
		 *
		 * Gets an array of all Challenges for a player. If the second
		 * argument is not given it gets all challenges. If the second
		 * argument is true it will get challenges where the player is 
		 * the challenger. If it is false it will get the challenges
		 * where the player is the challengee.
		 * @param $playerID The ID of the player
		 * @param $challenger Whether or not to get challenges where the
		 *                    user is the challenger. Omit to get all challenges
		 * @return Array of Challenge objects, or false on failure
		 */
		public static function getChallenges($playerID, $challenger = null){
			$database = DataLayer::getChallengeInstance();
			return $database->getChallenges($playerID, $challenger);
		}
		
		/**
		 * challenge($playerID, $challengeeID)
		 *
		 * Creates a challenge initiated by the current user.
		 *
		 * @param $challengerID The player initiating the challenge
		 * @param $challengeeID The player being challenged
		 * @return Whether or not the challenge was successfully added
		 *         to the database
		 */
		public function challenge($challengerID, $challengeeID){
			$database = DataLayer::getChallengeInstance();
			return $database->challenge($challengerID, $challengeeID);
		}

		/**
		 * updateChallengeStatus($challengerID, $challengeeID, $newStatus)
		 *
		 * Updates the status of a challenge
		 *
		 * @param $challengerID The player initiating the challenge
		 * @param $challengeeID The player being challenged
		 * @param $newStatus The new status of the challenge
		 * @return Whether or not the challenge was successfully modified
		 */
		public function updateChallengeStatus($challengerID, $challengeeID, $newStatus){
			$database = DataLayer::getChallengeInstance();
			return $database->updateChallengeStatus($challengerID, $challengeeID, $newStatus);
		}
		
		public function getLobbyPlayers(){
			$database = DataLayer::getChallengeInstance();
		}

	}

?>