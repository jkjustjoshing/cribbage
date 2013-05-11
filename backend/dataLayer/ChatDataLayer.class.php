<?php

	class ChatDataLayer extends DataLayer{
		
		private $mysql;
		
		public function __construct($mysqli){
			$this->mysqli = $mysqli;
		}
		
		public function getChats($userID, $opponentID, $lastSeenID = null){
			if($opponentID == 0){
				$sql = "SELECT 
					chats.id, 
					chats.poster, 
					chats.content,
					chats.timestamp,
					players.username
					FROM chats LEFT JOIN players ON players.id=chats.poster 
					WHERE chats.player1ID IS NULL AND chats.player2ID IS NULL ";
			}else{
				$sql = "SELECT 
					chats.id, 
					chats.poster, 
					chats.content, 
					chats.timestamp,
					players.username
					FROM chats LEFT JOIN players ON players.id=chats.poster
					WHERE chats.player1ID=? AND chats.player2ID=? ";
			}
			if($lastSeenID !== null){
				$sql .= " AND chats.id > ? ";
			}
			$sql .= "ORDER BY chats.timestamp DESC ";
			if($lastSeenID === null) $sql .= " LIMIT 50 ";
									
			if($stmt = $this->mysqli->prepare($sql)){
				
				//Bind paramaters
				//smaller ID is first
				
				if($userID < $opponentID){
					$player1 = $userID;
					$player2 = $opponentID;
				}else{
					$player1 = $opponentID;
					$player2 = $userID;
				}
				
				if($opponentID == 0){
					if($lastSeenID !== null){
						$stmt->bind_param("i", $lastSeenID);
					}
				}else{
					if($lastSeenID !== null){
						$stmt->bind_param("iii", $player1, $player2, $lastSeenID);
					}else{
						$stmt->bind_param("ii", $player1, $player2);
					}
				}
				
				$stmt->execute();
				
				$stmt->bind_result($id, $poster, $content, $timestamp, $username);
				
				$chatArr = array();
				while($stmt->fetch()){
				
					$timestamp = strtotime( $timestamp );
					$chatItem = array("id"=>$id, "posterID"=>$poster, "posterUsername"=>$username, "content"=>$content, "timestamp"=>$timestamp);
					$chatArr[] = $chatItem;
				}
				
				return $chatArr;
			}
			
			return false;
		}

		public function postChat($userID, $opponentID, $message){			
			
			// If this is being called statically
			if(!isset($this)){
				throw Exception("Calling DataLayer->postChat statically");
			}
			if($opponentID == 0){
				$sql = "INSERT INTO chats (poster, content, timestamp) VALUES (?, ?, ?)";
			}else{
				$sql = "INSERT INTO chats (player1ID, player2ID, poster, content, timestamp) VALUES (?, ?, ?, ?, ?)";
				if($userID < $opponentID){
					$player1 = $userID;
					$player2 = $opponentID;
				}else{
					$player1 = $opponentID;
					$player2 = $userID;
				}
			}
			
			if($stmt = $this->mysqli->prepare($sql)){
												
				//////////////////////////////////////////////
				/// MUST VALIDATE INPUT!!!!
				////////////////////////////////////////////
				
				$timestamp = date("Y-m-d H:i:s", time());
				
				//Bind paramater of username			
				if($opponentID == 0){
					$stmt->bind_param("iss",
						$userID,
						$message,
						$timestamp);
				}else{
					$stmt->bind_param("iiiss", 
						$player1, 
						$player2, 
						$userID,
						$message,
						$timestamp);
				}
								
				$stmt->execute();

				if($this->mysqli->insert_id === 0){
					//fail, throw exception
					throw new DatabaseException("New chat was not successfully posted to database. " .
					                            "User - $userID, Opponent - $opponentID, " .
					                            "Message - '$message'");
				}
				
			}
		}
	}
?>
