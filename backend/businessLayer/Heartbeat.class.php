<?

	require_once(BACKEND_DIRECTORY . "/dataLayer/DataLayer.class.php");

	/**
	 * Heartbeat class
	 * @author Josh Kramer
	 * Handles setting heartbeats and seeing who has 
	 * recently sent one for this room.
	 */
	class Heartbeat{
		
		public static function beat($playerID, $room){
			$result = DataLayer::getChallengeInstance()->setHeartbeat($playerID, $room);
		}

		public static function getOnlinePlayers($room){
			$players = DataLayer::getChallengeInstance()->getOnlinePlayers($room);
			$playerIDs = array();
			foreach($players as $player){
				$playerIDs[] = array("id"=>$player["id"], "username"=>$player["username"]);
			}

			return $playerIDs;
		}

		public static function getOnlinePlayerArr($room){
			return DataLayer::getChallengeInstance()->getOnlinePlayers($room);
		}

	}

?>