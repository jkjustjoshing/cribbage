<?php
	require_once(dirname(__FILE__) . "/../../../../SiteConfig.class.php");


	function resetDatabase(){
		$chats = "CREATE TABLE IF NOT EXISTS chats(
			player1ID INT NOT NULL,
			player2ID INT NOT NULL,
			PRIMARY KEY(player1ID, player2ID),
			poster INT NOT NULL,
			FOREIGN KEY(poster) REFERENCES players(id),
			content VARCHAR(1000),
			timestamp DATETIME
		)";
		
		$players = "CREATE TABLE IF NOT EXISTS players(
			id INT NOT NULL AUTO INCREMENT,
			PRIMARY KEY(id),
			username VARCHAR(30),
			email VARCHAR(100),
			password CHAR(50), --possibly change based on salt length
			receiveNotifications BOOLEAN
		)";
		
		$suits = "CREATE TABLE IF NOT EXISTS suits(
			id INT NOT NULL AUTO INCREMENT,
			PRIMARY KEY(id),
			value VARCHAR(7)
		)";
		
		$numbers = "CREATE TABLE IF NOT EXISTS numbers(
			id INT NOT NULL AUTO INCREMENT,
			PRIMARY KEY(id),
			value TINYINT
		)";
		
		$playingcards = "CREATE TABLE IF NOT EXISTS playingcards(
			id INT NOT NULL AUTO INCREMENT,
			PRIMARY KEY(id),
			suitID INT NOT NULL,
			FOREIGN KEY(suitID) REFERENCES suits(id),
			numberID INT NOT NULL,
			FOREIGN KEY(numberID) REFERENCES numbers(id)
		)";
		
		$carddecks = "CREATE TABLE IF NOT EXISTS carddecks(
			deckID INT NOT NULL AUTO INCREMENT
			index TINYINT,
			PRIMARY KEY(deckID, index),
			playingCardID INT,
			FOREIGN KEY(playingCardID) REFERENCES playingcards(id)
		)";
		
		
		$mysqli = new mysqli(
								SiteConfig::DATABASE_SERVER, 
								SiteConfig::DATABASE_USER, 
								SiteConfig::DATABASE_PASSWORD, 
								SiteConfig::DATABASE_DATABASE);
		
		$mysqli->query($chats);
		$mysqli->query($players);
		
	}
	
	resetDatabase();
	

?>
