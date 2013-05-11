<?php

	require_once(BACKEND_DIRECTORY . "/businessLayer/Heartbeat.class.php");

	function heartbeat($room){
		$room = intval($room);
		if($room < 0){
			return false;
		}

		$playerID = SecurityToken::extract();

		return Heartbeat::beat($playerID, $room);	
	}
?>
