<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/Chat.class.php");

	function getChat($data){
		$opponentID = intval($data["opponentID"]);
		$playerID = intval($data["playerID"]);
		$lastSeenID = intval($data["lastSeenID"]);
		$chatArr = array();

		// If the player is looking for a room other than the lobby, 
		// check the cookie ID
		if($opponentID !== 0){
			if(!SecurityToken::checkId($playerID)){
				return "You don't have permission to view this chat.";
			}
		}
		
		$room = ChatRoom::getChatRoom(intval($playerID), intval($opponentID), $lastSeenID);
		
		// Check if there was an error
		if($room === false){
			return "There was an error fetching the chats. Try reloading the page.";
		}
		
		foreach($room as $chat){
			$chatArr[] = $chat->toArray();
		}
		
		return $chatArr;
		
	}
	
	function postChat($data){
		$opponentID = intval($data["opponentID"]);
		$playerID = intval($data["playerID"]);
		$content = $data["content"];
		
		if(SecurityToken::checkId($playerID)){
			$post = ChatItem::post($playerID, $opponentID, $content);
			if($post === false){
				return "There was a database error posting the chat.";
			}
		}else{
			return "You don't have permission to post in this chat.";
		}
		
		$chatArr = getChat($data);
		
		return $chatArr;
		
	}
?>
