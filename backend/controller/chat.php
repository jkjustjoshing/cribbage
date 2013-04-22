<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/Chat.class.php");

	function getChat($data){
	
		$opponentID = $data["opponentID"];
		$playerID = $data["playerID"];
		$lastSeenTimestamp = $data["lastSeenTimestamp"];
		
		$room = ChatRoom::getChatRoom(intval($playerID), intval($opponentID), $lastSeenTimestamp);
		$chatArr = array();
		
		foreach($room as $chat){
			$chatArr[] = $chat->toArray();
		}
		
		return json_encode($chatArr, JSON_HEX_TAG);
		
	}
	/*
	class ChatController{
		

		public static getChats($userID, $opponentID, $lastTimestamp){

		}

		public static postChat($userID, $opponentID, $message){

		}

	}
*/
?>
