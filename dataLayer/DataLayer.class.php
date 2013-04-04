<?php

	require_once(dirname(__FILE__) . "/../chats/Chat.class.php");

	class DataLayer{
		
		protected $mysqli;

		public __construct($db_host, $db_username, $db_password, $db_database){
			$this->mysqli = new mysqli($db_host, $db_username, $db_password, $db_database);
		
			if($this->mysqli->connect_errno > 0){
				throw new RuntimeException("Could not connect to database");
			}

		}
		
		public getChats($userID, $opponentID, $lastSeenTimestamp){
			$sql = "SELECT 
				poster, content, timestamp 
				FROM chats 
				WHERE player1ID=? AND player2ID=? AND timestamp > ? 
				ORDER BY timestamp";
			
			$chatRoom = new ChatRoom();
			
			if($stmt = $this->mysqli->prepare($sql){
				
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

		public postChat($userID, $opponentID, $message0{
		
		}

		public __destruct(){
			$this->mysqli->close();
		}

	}


?>
