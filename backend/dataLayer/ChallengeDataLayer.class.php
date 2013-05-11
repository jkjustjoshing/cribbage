<?php

	require_once(dirname(__FILE__) . "/../SiteConfig.class.php");

	class ChallengeDataLayer extends DataLayer{
		
		private $mysql;
		
		public static $STATUS = array( //WHO SETS IT
							"PENDING", // challenger
							"VIEWED",  // challengee
							"ACCEPTED", //challengee
							"DENIED", //challengee
							"CANCELLED", //challenger
							"COMPLETED" //game in progress, challenger
						);
		
		public function __construct($mysqli){
			$this->mysqli = $mysqli;
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
		 * @return Array of challenges, false on failure
		 */
		public function getChallenges($playerID, $challenger = null){
			
			//Make sure the input is a valid number
			$playerID = intval($playerID);
			if($challenger !== false && $challenger !== true && $challenger !== null){
				return false;
			}
			
			$sql = "SELECT 
				challenges.challengerID AS challengerID, 
				challenges.challengeeID AS challengeeID, 
				challengestatuses.value AS status
				FROM 
				challenges LEFT JOIN challengestatuses
				ON challenges.challengestatusID=challengestatuses.id 
				WHERE ";
				
				
			if($challenger === false){
				$sql .= "challenges.challengeeID = ?";
			}else if($challenger === true){
				$sql .= "challenges.challengerID = ?";
			}else{
				$sql .= "challenges.challengerID = ? OR challenges.challengeeID = ?";
			}
			
			
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				if($challenger !== null){
					$stmt->bind_param("i", $playerID);
				}else{
					$stmt->bind_param("ii", $playerID, $playerID);
				}
				$stmt->execute();
				
				$stmt->bind_result($challengerID, $challengeeID, $status);
				
				$challengeArr = array();
				while($stmt->fetch()){
								
					$challengeItem = array(
						"challengerID"=>$challengerID, 
						"challengeeID"=>$challengeeID, 
						"status"=>$status
					);
					$challengeArr[] = $challengeItem;
				}
				
				return $challengeArr;
			}
			
			return false;
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
			
			//Make sure the inputs are valid numbers
			$challengerID = intval($challengerID);
			$challengeeID = intval($challengeeID);
			
			$sql = "INSERT INTO challenges 
				(challengerID, challengeeID, challengestatusID)
				VALUES
				(?,?,(SELECT id FROM challengestatuses WHERE value='PENDING'))
				
				ON DUPLICATE KEY
				UPDATE challengestatusID=(SELECT id FROM challengestatuses WHERE value='PENDING')";
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				$stmt->bind_param("ii", $challengerID, $challengeeID);
				
				$stmt->execute();

				if($this->mysqli->affected_rows === 0){
					return false;
				}
				
				return true;
			}
			
			return false;
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
			//Make sure the inputs are valid numbers
			$challengerID = intval($challengerID);
			$challengeeID = intval($challengeeID);
			
			if(!in_array($newStatus, self::$STATUS)){
				return false;
			}
			
			$sql = "UPDATE challenges SET challengestatusID=(
				SELECT id FROM challengestatuses WHERE value=?
			) WHERE challengerID=? AND challengeeID=?";
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				$stmt->bind_param("sii", $newStatus, $challengerID, $challengeeID);
				
				$stmt->execute();

				if($this->mysqli->affected_rows === 0){
					return false;
				}
				
				return true;
			}
			
			return false;
			
		}
		
		public function setHeartbeat($playerID, $room){
			$playerID = intval($playerID);
			$room = intval($room);

			$sql = "INSERT INTO heartbeats (playerID, room, lastSeen) VALUES (?,?, NOW()) 
				ON DUPLICATE KEY 
				UPDATE lastSeen=VALUES(lastSeen)";

			if($stmt = $this->mysqli->prepare($sql)){
				
				$stmt->bind_param("ii", $playerID, $room);
				
				$stmt->execute();

				if($this->mysqli->affected_rows === 0){
					return false;
				}
				
				return true;
			}
			return false;

		}


		public function getOnlinePlayers($room){
			//Make sure the input is a valid number
			$room = intval($room);

			if($room < 0){
				return false;
			}
			
			$sql = "SELECT
					players.id, 
					players.username, 
					players.email, 
					players.receiveNotifications,
					heartbeats.lastSeen
					FROM heartbeats LEFT JOIN players ON players.id=heartbeats.playerID
					WHERE heartbeats.room=? AND heartbeats.lastSeen > DATE_SUB(NOW(), INTERVAL ".SiteConfig::HEARTBEAT_DELAY_UNTIL_OFFLINE." SECOND)";			
			
			
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				$stmt->bind_param("i", $room);
				$stmt->execute();
				
				$stmt->bind_result($id, $username, $email, $receiveNotifications, $lastSeen);
				
				$playerArr = array();
				while($stmt->fetch()){
					$player = array(
						"id"=>$id, 
						"username"=>$username, 
						"email"=>$email,
						"receiveNotifications"=>$receiveNotifications
					);
					$playerArr[] = $player;
				}

				return $playerArr;
			}
			
			return false;

		}

		public function isUserHere($playerID, $room){
			//Make sure the input is a valid number
			$room = intval($room);
			$playerID = intval($playerID);

			if($room < 0){
				return false;
			}
			
			$sql = "SELECT
					players.id
					FROM heartbeats LEFT JOIN players ON players.id=heartbeats.playerID
					WHERE heartbeats.room=? AND heartbeats.playerID=? AND heartbeats.lastSeen > DATE_SUB(NOW(), INTERVAL ".SiteConfig::HEARTBEAT_DELAY_UNTIL_OFFLINE." SECOND)";			
			
			
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				$stmt->bind_param("ii", $room, $playerID);
				$stmt->execute();
				
				$stmt->store_result();
				
				return $stmt->num_rows() === 1;
			}
			
			return false;
		}
	}
?>
