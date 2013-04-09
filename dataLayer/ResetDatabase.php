<?php
	require_once(dirname(__FILE__) . "/../../../../SiteConfig.class.php");

	$queries = array();
	$queries["players"] = "CREATE TABLE IF NOT EXISTS players(
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		username VARCHAR(30),
		email VARCHAR(100),
		password CHAR(60), " . //--possibly change based on salt length
		"receiveNotifications BOOLEAN, 
		UNIQUE KEY(username)
	);";
	
	$queries["chats"] = "CREATE TABLE IF NOT EXISTS chats(
		player1ID INT NOT NULL,
		player2ID INT NOT NULL,
		PRIMARY KEY(player1ID, player2ID),
		poster INT NOT NULL,
		FOREIGN KEY(poster) REFERENCES players(id),
		content VARCHAR(1000),
		timestamp DATETIME
	);";
	
	$queries["suits"] = "CREATE TABLE IF NOT EXISTS suits(
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		value VARCHAR(7)
	);";
	
	$queries["numbers"] = "CREATE TABLE IF NOT EXISTS numbers(
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		value TINYINT
	);";
	
	$queries["playingcards"] = "CREATE TABLE IF NOT EXISTS playingcards(
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		suitID INT NOT NULL,
		FOREIGN KEY(suitID) REFERENCES suits(id),
		numberID INT NOT NULL,
		FOREIGN KEY(numberID) REFERENCES numbers(id)
	);";
	
	$queries["carddecks"] = "CREATE TABLE IF NOT EXISTS carddecks(
		deckID INT NOT NULL,
		cardIndex TINYINT(1) NOT NULL,
		PRIMARY KEY(deckID, cardIndex),
		playingCardID INT,
		FOREIGN KEY(playingCardID) REFERENCES playingcards(id)
	);";
	
	
	

	function addTables($print = false){
	$mysqli = new mysqli(
								SiteConfig::DATABASE_SERVER, 
								SiteConfig::DATABASE_USER, 
								SiteConfig::DATABASE_PASSWORD, 
								SiteConfig::DATABASE_DATABASE);
		global $queries;
	
		if($mysqli == null){
			echo "fail";
		}
		if($print) echo "Adding tables<ul>";
		foreach($queries as $table=>$query){
			$mysqli->query($query) or die($mysqli->error);
			if($print) echo "<li>" . $table . "</li>";
		}
		if($print) echo "</ul>";
		$mysqli->close();
		
	}
	
	function dropAllTables($print = false){
	$mysqli = new mysqli(
								SiteConfig::DATABASE_SERVER, 
								SiteConfig::DATABASE_USER, 
								SiteConfig::DATABASE_PASSWORD, 
								SiteConfig::DATABASE_DATABASE);
		$queries = array_reverse($GLOBALS["queries"]);
	
		if($print) echo "Dropping tables:<ul>";
		foreach($queries as $key=>$query){
			$mysqli->query("drop table " . $key . ";");
			if($print) echo "<li>drop table " . $key . ";</li>";
		}
		if($print) echo "</li>";
		$mysqli->close();
		
	}
		
	
	if(isset($_GET["drop"])){
		dropAllTables(true);
	}
	if(isset($_GET["add"])){
		addTables(true);
	}
	
?>
