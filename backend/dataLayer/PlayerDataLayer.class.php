<?php

	class PlayerDataLayer{
		
		private $mysqli;
		const SALT_LENGTH = 20;

				
		public function __construct($mysqli){
			$this->mysqli = $mysqli;
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

?>
