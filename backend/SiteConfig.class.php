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
						"getTurn", // Are we waiting for the other user to do something (either put a card down, put cards in crib, accept points they are viewing)
		/* good */				"getDealer", // Returns if I am the dealer
						"getCutCard", // Gets the cut card, or null if there isn't one
						"getPeggingCards", // Get the cards for pegging
		/* good */				"getScore", // Get the scores of the two players
		/* good */				"getDealer", // Who is the dealer now?
						"getGameData", // A large array of everything you could want to know about the board state
		/* good */				"getGameState", // The current state of the game's state machine
						"pickCutIndex",  // Send an index of 
		/* good */				"shuffle", // Shuffle the deck. Pass the number of times to shuffle to reduce requests
						"playCard", // Put a card on the table for pegging. Only remove from hand if it returns success (could return a required "go", a "not your turn", "you don't have that card")
						"putInCrib", // Send 2 cards to put into the crib
						"deal" // Deal the cards (only if you are the dealer)
						),
			"challenge" => array(
						"getChallenges", 
						"challenge",
						"updateChallengeStatus",
						"getOnlinePlayers"
						)
						
						
						);


	}


?>
