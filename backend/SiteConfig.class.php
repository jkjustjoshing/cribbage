<?php

	/**
	 * SiteConfig
	 *
	 * This class provides the configuration variables for the site
	 * without polluting the global namespace. Additionally, this
	 * provides the database connection.
	 * For security, this file will be included by the Business Logic Layer,
	 * but will only be used in the Data Logic Layer. The Data Logic Layer
	 * will not work unless it is accessed through the Business Logic Layer.
	 */
	class SiteConfig{
	
		// Database constants
		const DATABASE_PASSWORD = "root";//"hug0War";
		const DATABASE_SERVER = "localhost";
		const DATABASE_USER = "root";//"jdk3414";
		const DATABASE_DATABASE = "jdk3414";


		// Amount of time since last heartbeat to mark someone as offline (integer seconds)
		const HEARTBEAT_DELAY_UNTIL_OFFLINE = 8;

		public static $POSSIBLE_METHODS = array(
			"chat" => array(
						"getChat", 
						"postChat"), 
			"game" => array(
						"getTurn", 
						"getGameState", 
						"pickCutIndex", 
						"playCard", 
						"putInCrib", 
						"deal"),
			"challenge" => array(
						"getChallenges", 
						"challenge",
						"updateChallengeStatus",
						"getOnlinePlayers"
						)
						
						
						);


	}


?>
