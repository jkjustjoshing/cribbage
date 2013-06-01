<?php

	require_once(dirname(__FILE__) . "/../businessLayer/Heartbeat.class.php");

	function heartbeat($room){
		$room = intval($room);
		if($room < 0){
			return false;
		}

		if(!SecurityToken::isTokenSet()){
			return false;
		}

		$playerID = SecurityToken::extract();

		return Heartbeat::beat($playerID, $room);	
	}
?>
