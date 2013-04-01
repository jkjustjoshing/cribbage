<?php

	require_once(dirname(__FILE__) . "/DataLayer.class.php");

	class ChatDataLayer extends DataLayer{
		
		public __construct($db_host, $db_username, $db_password, $db_database){
			parent::_construct($db_host, $db_username, $db_password, $db_database);
		}

		public getChats($userID, $opponentID, $lastSeenTimestamp = -1

	}
	

?>
