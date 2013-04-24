<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/Chat.class.php");

	function getChat($data){
	
		$opponentID = $data["opponentID"];
		$playerID = $data["playerID"];
		$lastSeenID = $data["lastSeenID"];
		$chatArr = array();

		// If the player is looking for a room other than the lobby, 
		// check the cookie ID
		if($opponentID !== 0 || $playerID !== 0){
			if(!SecurityToken::checkId($playerID)){
				return $chatArr;
			}
		}
		
		$room = ChatRoom::getChatRoom(intval($playerID), intval($opponentID), $lastSeenID);
		
		foreach($room as $chat){
			$chatArr[] = $chat->toArray();
		}
		
		return $chatArr;
		
	}
	
	function postChat($data){
		$opponentID = $data["opponentID"];
		$playerID = $data["playerID"];
		$content = $data["content"];
		
		if(SecurityToken::checkId($playerID)){
			$post = ChatItem::post(intval($playerID), intval($opponentID), $content);
		}
		
		$chatArr = getChat($data);
		
		return $chatArr;
		
	}
?>
