<?php
	require_once(dirname(__FILE__) . "/../SiteConfig.class.php");
	require_once(dirname(__FILE__) . "/../dataLayer/DataLayer.class.php");

	$queries = array();
	$queries["players"] = "CREATE TABLE IF NOT EXISTS players(
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		username VARCHAR(30),
		email VARCHAR(100),
		password CHAR(40), 
		salt CHAR(".PlayerDataLayer::SALT_LENGTH."),
		receiveNotifications BOOLEAN,
		lobbyHeartbeat DATETIME,
		
		UNIQUE KEY(username)
	);";
	
	$queries["chats"] = "CREATE TABLE IF NOT EXISTS chats(
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		player1ID INT,
		player2ID INT,
		poster INT NOT NULL,
		FOREIGN KEY(player1ID) REFERENCES players(id),
		FOREIGN KEY(player2ID) REFERENCES players(id),
		FOREIGN KEY(poster) REFERENCES players(id),
		content VARCHAR(1000),
		timestamp DATETIME
	);";
	
	$queries["playingcards"] = "CREATE TABLE IF NOT EXISTS playingcards(
		id INT NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		suit VARCHAR(8) NOT NULL,
		number INT NOT NULL
	);";
	
	$queries["carddecks"] = "CREATE TABLE IF NOT EXISTS carddecks(
		gameID INT NOT NULL,
		cardIndex TINYINT(1) NOT NULL,
		PRIMARY KEY(gameID, cardIndex),
		playingCardID INT,
		FOREIGN KEY(playingCardID) REFERENCES playingcards(id)
	);";
	
	$queries["gamestatuses"] = "CREATE TABLE IF NOT EXISTS gamestatuses(
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		value VARCHAR(15)
		);";

	$queries["gamestates"] = "CREATE TABLE IF NOT EXISTS gamestates(
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		value VARCHAR(20)
		);";
	
	$queries["gamespaces"] = "CREATE TABLE IF NOT EXISTS gamespaces(
		id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
		player1ID INT NOT NULL,
		FOREIGN KEY(player1ID) REFERENCES players(id),
		player2ID INT NOT NULL,
		FOREIGN KEY(player2ID) REFERENCES players(id),
		player1Score TINYINT DEFAULT 0,
		player1backPinPosition TINYINT DEFAULT 0,
		player2Score TINYINT DEFAULT 0,
		player2backPinPosition TINYINT DEFAULT 0,
		turnID INT NOT NULL,
		FOREIGN KEY(turnID) REFERENCES players(id),
		dealerID INT NOT NULL,
		FOREIGN KEY(dealerID) REFERENCES players(id),
		cutCard INT,
		FOREIGN KEY(cutCard) REFERENCES playingcards(id),
		gamestatusID INT NOT NULL,
		FOREIGN KEY(gamestatusID) REFERENCES gamestatuses(id),
		gamestateID INT NOT NULL,
		FOREIGN KEY(gamestateID) REFERENCES gamestates(id)
	);";

	$queries["playerhands"] = "CREATE TABLE IF NOT EXISTS playerhands(
		gameID INT NOT NULL COMMENT 'The gameID this hand exists for',
		FOREIGN KEY(gameID) REFERENCES gamespaces(id),
		playerID INT COMMENT 'NULL if the hand is the crib',
		FOREIGN KEY(playerID) REFERENCES players(id),
		playingcardID INT NOT NULL,
		FOREIGN KEY(playingcardID) REFERENCES playingcards(id),
		inHand TINYINT COMMENT 'Indicates if the card has been pegged yet, 0 or 1'
	);";
	
	$queries["playedcards"] = "CREATE TABLE IF NOT EXISTS playedcards(
		gameID INT NOT NULL,
		FOREIGN KEY(gameID) REFERENCES gamespaces(id),
		cardOrder INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		playingcardID INT,
		FOREIGN KEY(playingcardID) REFERENCES playingcards(id), 
		playedByID INT NOT NULL,
		FOREIGN KEY(playedByID) REFERENCES players(id)
	);";
	
	$queries["challengestatuses"] = "CREATE TABLE IF NOT EXISTS challengestatuses(
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		value VARCHAR(15)
	);";
	
	$queries["challenges"] = "CREATE TABLE IF NOT EXISTS challenges(
		challengerID INT NOT NULL,
		FOREIGN KEY(challengerID) REFERENCES players(id),
		challengeeID INT NOT NULL,
		FOREIGN KEY(challengeeID) REFERENCES players(id),
		PRIMARY KEY(challengeeID, challengerID),
		challengestatusID INT NOT NULL,
		FOREIGN KEY(challengestatusID) REFERENCES challengestatuses(id)
	);";

	$queries["heartbeats"] = "CREATE TABLE IF NOT EXISTS heartbeats(
		playerID INT NOT NULL,
		FOREIGN KEY(playerID) REFERENCES players(id),
		room INT NOT NULL,
		PRIMARY KEY(playerID, room),
		lastSeen TIMESTAMP
	);";

	$queries["populategamestatuses"] = 
		"INSERT INTO gamestatuses (value) VALUES 
			('INVITED'), 
			('IN_PROGRESS'), 
			('FINISHED'), 
			('FOREFIT'), 
			('CANCEL');";

	$queries["populategamestates"] = 
		"INSERT INTO gamestates (value) VALUES 
			('DEALING'), 
			('CHOOSING_CRIB'), 
			('CUTTING_CARD'), 
			('PEGGING'),  
			('VIEWING_HANDS'),  
			('WAITING_PLAYER_1'),  
			('WAITING_PLAYER_2');";

	$queries["populatechallengestatuses"] =
		"INSERT INTO challengestatuses (value) values ";
	
	$first = true;
	foreach(ChallengeDataLayer::$STATUS as $status){
		if($first) $first = false;
		else $queries["populatechallengestatuses"] .= ", ";
		$queries["populatechallengestatuses"] .= "('" . $status . "')";
	}


	$queries["populateCards"] = 
		"INSERT INTO playingcards (suit, number) VALUES	";
		
	$suits = array(
			"diamond",
			"club",
			"heart",
			"spade"
	);
		
	$first = true;
	foreach($suits as $suit){
		for($i = 1; $i <= 13; ++$i){
			if(!$first) $queries["populateCards"] .= ", ";
			else $first = false;
			
			$queries["populateCards"] .= "('" . $suit . "', " . $i . ")";
		}
	}

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
