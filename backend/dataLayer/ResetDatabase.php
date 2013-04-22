<?php
	require_once(dirname(__FILE__) . "/../SiteConfig.class.php");
	require_once(dirname(__FILE__) . "/DataLayer.class.php");

	$queries = array();
	$queries["players"] = "CREATE TABLE IF NOT EXISTS players(
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		username VARCHAR(30),
		email VARCHAR(100),
		password CHAR(40), 
		salt CHAR(".DataLayer::SALT_LENGTH."),
		receiveNotifications BOOLEAN,
		lobbyHeartbeat DATETIME,
		
		UNIQUE KEY(username)
	);";
	
	$queries["chats"] = "CREATE TABLE IF NOT EXISTS chats(
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		player1ID INT NOT NULL,
		player2ID INT NOT NULL,
		poster INT NOT NULL,
		FOREIGN KEY(player1ID) REFERENCES players(id),
		FOREIGN KEY(player2ID) REFERENCES players(id),
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
	
	$queries["challenges"] = "CREATE TABLE IF NOT EXISTS challenges(
		challengerID INT NOT NULL,
		challengeeID INT NOT NULL, 
		time DATETIME,
		PRIMARY KEY(challengerID, challengeeID),
		FOREIGN KEY(challengerID) REFERENCES players(id),
		FOREIGN KEY(challengeeID) REFERENCES players(id)
	);";
	
	$queries["statuses"] = "CREATE TABLE IF NOT EXISTS statuses(
		id INT NOT NULL AUTO_INCREMENT,
		value VARCHAR(15),
		 PRIMARY KEY(id)
	);";
	
	$queries["gamespaces"] = "CREATE TABLE IF NOT EXISTS gamespaces(
		id INT NOT NULL PRIMARY KEY,
		player1ID INT NOT NULL,
		FOREIGN KEY(player1ID) REFERENCES players(id),
		player2ID INT NOT NULL,
		FOREIGN KEY(player2ID) REFERENCES players(id),
		player1Score TINYINT DEFAULT 0,
		player2Score TINYINT DEFAULT 0,
		deckID INT NOT NULL,
		cribID INT NOT NULL,
		" ./*FOREIGN KEY(cribID) REFERENCES playerhand(id),*/ "
		turnID INT NOT NULL,
		FOREIGN KEY(turnID) REFERENCES players(id),
		dealerID INT NOT NULL,
		FOREIGN KEY(dealerID) REFERENCES players(id),
		statusID INT NOT NULL,
		FOREIGN KEY(statusID) REFERENCES statuses(id)
	);";
	
	
	$queries["populateStatuses"] = 
		"INSERT INTO statuses (value) VALUES ('INVITED'), ('IN_PROGRESS'), ('FINISHED'), ('FOREFIT'), ('CANCEL');";

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
