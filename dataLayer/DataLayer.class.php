<?php

	require_once(dirname(__FILE__) . "/../../../../SiteConfig.class.php");
	require_once(dirname(__FILE__) . "/../chats/Chat.class.php");

	class DataLayer{
		
		private $mysqli;
		private static $instance;

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
		 * getUser($userIdentifier)
		 * 
		 * Gets the user with either a string username
		 * or a numeric userID.
		 * @return array with all data on success, false on failure
		 */

		public function getUser($userIdentifier){
			
			$integer = is_int($userIdentifier);
			
			$sql = "SELECT 
				id, username, email, password, receiveNotifications
				FROM players ";
			if($integer) $sql .= " WHERE id=?";
			else $sql .= " WHERE username=?";
			
			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramater of username				
				$stmt->bind_param(($integer ? "i" : "s"), $userIdentifier);
				
				$stmt->execute();
				
				$stmt->bind_result($id, $username, $email, $password, $receiveNotifications);
				
				if($stmt->num_rows != 1){
					// Either 0 or more than 1 user (should never happen)
					return false;
				}
				
				// Only one result set should be fetched
				$stmt->fetch();
				
				return array("id"=>$id, 
				             "username"=>$username, 
				             "email"=>$email, 
				             "password"=>$password,
				             "receiveNotifications"=>$receiveNotifications
				             );
				
			}
			
		}

		public function __destruct(){
			$this->mysqli->close();
		}

	}


?>
