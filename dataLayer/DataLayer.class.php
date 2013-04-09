<?php

	require_once(dirname(__FILE__) . "/../../../../SiteConfig.class.php");
	require_once(dirname(__FILE__) . "/../chats/Chat.class.php");

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
		
		public function getChats($userID, $opponentID, $lastSeenTimestamp){
			$sql = "SELECT 
				poster, content, timestamp 
				FROM chats 
				WHERE player1ID=? AND player2ID=? AND timestamp > ? 
				ORDER BY timestamp";
			
			$chatRoom = new ChatRoom();
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramaters
				//smaller ID is first
				
				$ordered = $userID < $opponentID;
				$stmt->bind_param("iii", ($ordered ? $userID : $opponentID), ($ordered ? $opponentID : $userID), $lastSeenTimestamp);
				
				$stmt->execute();
				
				$stmt->bind_result($poster, $content, $timestamp);
				
				while($stmt->fetch()){
					$chatItem = new ChatItem($poster, $content, $timestamp);
					$chatRoom->addItem($chatItem);
				}
			}
			
			return $chatRoom;
		}

		public function postChat($userID, $opponentID, $message){
		
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
				
				if($stmt->num_rows != 1){
					// Either 0 or more than 1 user (should never happen)
					return false;
				}
				
				// Only one result set should be fetched
				$stmt->fetch();
				
				return array("id"=>$id, 
				             "username"=>$username, 
				             "email"=>$email, 
				             "receiveNotifications"=>$receiveNotifications
				             );
				
			}
			
			echo "FAILLLL";
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
			
			$hashedPassword = self::saltAndHash($password);
			
			$sql = "INSERT INTO players (username, password, email, receiveNotifications) VALUES (?, ?, ?, true)";
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramater of username				
				$stmt->bind_param("sss", $username, $hashedPassword, $email);
				
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
				password
				FROM players WHERE username=?";

			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramater of username				
				$stmt->bind_param("s", $username);
				
				$stmt->execute();
				
				$stmt->bind_result($storedPassword);
				
				// Only one result set should be fetched
				$stmt->fetch();
				
				$salt = substr($storedPassword, self::SALT_LENGTH * -1);
						
				$hashedPassword = sha1($enteredPassword . $salt);
			
				return ($hashedPassword . $salt) == $storedPassword;
				
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
		 */
		private function saltAndHash($newPassword){
			$possibleCharacters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$saltString = "";
			
			for($i = 0; $i < self::SALT_LENGTH; ++$i){
				$saltString .= substr($possibleCharacters, mt_rand(0, strlen($possibleCharacters)), 1);
			}
			
			$hashedPassword = sha1($newPassword . $saltString);
						
			return $hashedPassword . $saltString;
		}

		public function __destruct(){
			$this->mysqli->close();
		}

	}


?>
