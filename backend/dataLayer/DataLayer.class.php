<?php

	require_once(BACKEND_DIRECTORY . "/SiteConfig.class.php");

	class DataLayer{
		
		private $mysqli;
		private static $instance;
		const SALT_LENGTH = 20;

		

		private function __construct($db_host, $db_username, $db_password, $db_database){
			$this->mysqli = new mysqli($db_host, $db_username, $db_password, $db_database);

			if($this->mysqli->connect_errno > 0){
				throw new RuntimeException("Could not connect to database");
			}

		}
		
		public static function getInstance(){
			if(self::$instance === null){
				self::$instance = new DataLayer(
							SiteConfig::DATABASE_SERVER,
							SiteConfig::DATABASE_USER,
							SiteConfig::DATABASE_PASSWORD,
							SiteConfig::DATABASE_DATABASE
						);
			}
			
			return self::$instance;
		}
		
		public function getChats($userID, $opponentID, $lastSeenTimestamp = null){
			$sql = "SELECT 
				poster, content, timestamp 
				FROM chats 
				WHERE player1ID=? AND player2ID=? ";
			if($lastSeenTimestamp !== null) $sql .= " AND timestamp > ? ";
			$sql .= "ORDER BY timestamp ";
			if($lastSeenTimestamp === null) $sql .= " LIMIT 50 ";
			
						
			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramaters
				//smaller ID is first
				
				if( $userID < $opponentID){
					$player1 = $userID;
					$player2 = $opponentID;
				}else{
					$player1 = $opponentID;
					$player2 = $userID;
				}
				
				
				if($lastSeenTimestamp !== null){
					$stmt->bind_param("iii", $player1, $player2, $lastSeenTimestamp);
				}else{
					$stmt->bind_param("ii", $player1, $player2);
				}
				$stmt->execute();
				
				$stmt->bind_result($poster, $content, $timestamp);
				
				$chatArr = array();
				while($stmt->fetch()){
					$chatItem = array("poster"=>$poster, "content"=>$content, "timestamp"=>$timestamp);
					$chatArr[] = $chatItem;
				}
				
				return $chatArr;
			}
			
			return false;
		}

		public function postChat($userID, $opponentID, $message){			
			$sql = "INSERT INTO chats (player1ID, player2ID, poster, content, timestamp) VALUES (?, ?, ?, ?, ?)";
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				if( $userID < $opponentID){
					$player1 = $userID;
					$player2 = $opponentID;
				}else{
					$player1 = $opponentID;
					$player2 = $userID;
				}
				
				$timestamp = date( 'Y-m-d H:i:s', time());
				
				//////////////////////////////////////////////
				/// MUST VALIDATE INPUT!!!!
				////////////////////////////////////////////
				
				//Bind paramater of username			
				$stmt->bind_param("sssss", 
					$player1, 
					$player2, 
					$userID,
					$message,
					$timestamp);
				
				$stmt->execute();
				
				if($this->mysqli->insert_id === 0){
					//fail, throw exception
					throw new DatabaseException("New chat was not successfully posted to database. " .
					                            "User - $userID, Opponent - $opponentID, " .
					                            "Message - '$message'");
				}
				
			}
		}
		
		/**
		 * getPlayer($userIdentifier)
		 * 
		 * Gets the player with either a string username
		 * or a numeric playerID.
		 * @param $playerIdentifier Either a numeric ID of user (for token calls)
		 *                          or a string username for login.
		 * @return array with all data on success, false on failure
		 */
		public function getPlayer($playerIdentifier){
			
			$integer = is_int($playerIdentifier);
			$sql = "SELECT 
				id, username, email, receiveNotifications
				FROM players ";
			if($integer) $sql .= " WHERE id=?";
			else $sql .= " WHERE username=?";
			
			if($stmt = $this->mysqli->prepare($sql)){
				//Bind paramater of username				
				$stmt->bind_param(($integer ? "i" : "s"), $playerIdentifier);
				
				$stmt->execute();
				
				$stmt->bind_result($id, $username, $email, $receiveNotifications);
				
				if($stmt->fetch() === null){
					// No results exist
					return false;
				}
				
				$arr = array("id"=>$id, 
				             "username"=>$username, 
				             "email"=>$email, 
				             "receiveNotifications"=>$receiveNotifications
				             );

				if($stmt->fetch() !== null){
					// There was more than 1 result
					return false;
				}

				return $arr;
				
			}
			
			return false;
			
		}
		
		/**
		 * addPlayer($username, $hashedPassword, $email)
		 * 
		 * Adds player to database. Data MUST be sanitized
		 * before passing to this method. 
		 * (Data SHOULD be validated here. It's validated in
		 * Player class, so that should be good enough!)
		 * @param $username The user's desired username
		 * @param $password The plaintext password (will be hashed)
		 * @param $email The user's email
		 * @return true if successfull add, false if not
		 */
		public function addPlayer($username, $password, $email){
			
			$passwordComponents = self::saltAndHash($password);
			
			$sql = "INSERT INTO players (username, password, salt, email, receiveNotifications) VALUES (?, ?, ?, ?, true)";
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramater of username				
				$stmt->bind_param("ssss", $username, $passwordComponents["password"], $passwordComponents["salt"], $email);
				
				$stmt->execute();
				
				$newPlayerID = $this->mysqli->insert_id;
				
				return !($newPlayerID == 0);
				
			}
		}

		/**
		 * checkPassword($username, $enteredPassword)
		 * 
		 * Salts the entered password, hashes, 
		 * and compares to stored password.
		 * @param $username The username of the user to check password of
		 * @param $enteredPassword The password entered by the user to try to log in
		 * @return boolean, whether or not the user entered the correct password
		 */
		public function checkPassword($username, $enteredPassword){
					
			$sql = "SELECT 
				password, salt
				FROM players WHERE username=?";

			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramater of username				
				$stmt->bind_param("s", $username);
				
				$stmt->execute();
				
				$stmt->bind_result($storedPassword, $salt);
				
				// Only one result set should be fetched
				$stmt->fetch();
						
				$hashedPassword = sha1($enteredPassword . $salt);
						
				return $hashedPassword == $storedPassword;
				
			}else{
				// Database error, assume password is wrong
				return false;
			}
		
		}
		
		/**
		 * saltAndHash($newPassword)
		 * 
		 * Takes a user entered password, generates salt, hashes password,
		 * and returns hash with salt already appended
		 * @param $newPassword The password to salt
		 * @return array with keys "password" with the hashed password and "salt" with the salt
		 */
		private function saltAndHash($newPassword){
			$possibleCharacters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$saltString = "";
			
			for($i = 0; $i < self::SALT_LENGTH; ++$i){
				$saltString .= substr($possibleCharacters, mt_rand(0, strlen($possibleCharacters)), 1);
			}
						
			$hashedPassword = sha1($newPassword . $saltString);
						
			return array("password"=>$hashedPassword, "salt"=>$saltString);
		}

		public function __destruct(){
			$this->mysqli->close();
		}

	}


	class DatabaseException extends Exception{
		public function __construct($message, $code = 0, Exception $previous = null){
			parent::__construct($message, $code, $previous);
		}
		
		public function __toString(){
			return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
		}
	}

?>
