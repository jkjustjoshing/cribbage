<?php

	require_once("../../frontend/config.php");

	require_once(dirname(__FILE__) . "/../simpletest/autorun.php");
	require_once(dirname(__FILE__) . "/../dataLayer/DataLayer.class.php");
	require_once(dirname(__FILE__) . "/ResetDatabase.php");

	
	class TestDataLayer extends UnitTestCase{
		function start(){
			dropAllTables();
			addTables();
		}
		
		function testInitialize(){
			echo "testInitialize()<br />";
			$this->start();
			
			$database = DataLayer::getChallengeInstance();
			$this->assertTrue(is_object($database));
			$this->assertEqual(get_class($database), "ChallengeDataLayer");
		}

		function testChallenge(){
			echo "testCreateChallenge()<br />";
			$this->start();
			
			$database = DataLayer::getChallengeInstance();
			
			$playerDatabase = DataLayer::getPlayerInstance();
			
			$playerDatabase->addPlayer("josh1", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh2", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh3", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh4", "josh1", "foo@foo.foo");
			
			$josh1 = $playerDatabase->getPlayer("josh1");
			$josh2 = $playerDatabase->getPlayer("josh2");
			$josh3 = $playerDatabase->getPlayer("josh3");
			$josh4 = $playerDatabase->getPlayer("josh4");
			
			$i = $database->challenge($josh1["id"], $josh2["id"]);
			$this->assertTrue($i);
			$i = $database->challenge($josh2["id"], $josh1["id"]);
			$this->assertTrue($i);
			$i = $database->challenge($josh2["id"], $josh3["id"]);
			$this->assertTrue($i);
			$i = $database->challenge($josh3["id"], $josh1["id"]);
			$this->assertTrue($i);
			
			$challenges = $database->getChallenges($josh1["id"]);
			$this->assertEqual(count($challenges), 3);
			
			$challenges = $database->getChallenges($josh1["id"], true);
			$this->assertEqual(count($challenges), 1);
			
			$challenges = $database->getChallenges($josh1["id"], false);
			$this->assertEqual(count($challenges), 2);
			
			$challenges = $database->getChallenges($josh2["id"]);
			$this->assertEqual(count($challenges), 3);
			
			$challenges = $database->getChallenges($josh2["id"], true);
			$this->assertEqual(count($challenges), 2);
			
			$challenges = $database->getChallenges($josh2["id"], false);
			$this->assertEqual(count($challenges), 1);
			
			$challenges = $database->getChallenges($josh4["id"]);
			$this->assertEqual(count($challenges), 0);
			
		}
		
		function testUpdateStatus(){
			echo "testUpdateStatus()<br />";
			
			//Rely on previous test - don't call $this->start()
			
			$database = DataLayer::getChallengeInstance();
			$playerDatabase = DataLayer::getPlayerInstance();
			
			
			$josh3 = $playerDatabase->getPlayer("josh3");
			
			$challenges = $database->getChallenges($josh3["id"], true);
			$this->assertEqual(count($challenges), 1);
			$this->assertEqual($challenges[0]["status"], "PENDING");
			$database->updateChallengeStatus($challenges[0]['challengerID'], $challenges[0]['challengeeID'], "DENIED");
			$challenges = $database->getChallenges($josh3["id"], true);
			$this->assertEqual($challenges[0]["status"], "DENIED");
			
			$database->challenge($challenges[0]['challengerID'], $challenges[0]['challengeeID']);
			$challenges = $database->getChallenges($josh3["id"], true);
			$this->assertEqual($challenges[0]["status"], "PENDING");
			
		}

		function testHeartbeat(){
			echo "test Heartbeat()<br />";

			//Rely on users from previous 2 tests - don't call $this->start()

			$database = DataLayer::getChallengeInstance();
			$playerDatabase = DataLayer::getPlayerInstance();

			$josh1 = $playerDatabase->getPlayer("josh1");
			$josh2 = $playerDatabase->getPlayer("josh2");
			$josh3 = $playerDatabase->getPlayer("josh3");
			$josh4 = $playerDatabase->getPlayer("josh4");

//t=0
			$database->setHeartbeat($josh1["id"], 0);
			sleep(SiteConfig::HEARTBEAT_DELAY_UNTIL_OFFLINE - 5); 
//t=5
			$database->setHeartbeat($josh2["id"], 0);
			sleep(2);
//t=7 (8 is limit)
			$database->setHeartbeat($josh3["id"], 0);
			$database->setHeartbeat($josh4["id"], 1);

			$this->assertTrue($database->isUserHere($josh4["id"], 1));
			$this->assertTrue($database->isUserHere($josh3["id"], 0));
			$this->assertTrue(!$database->isUserHere($josh4["id"], 0));
			$this->assertTrue(!$database->isUserHere($josh3["id"], 1));


			$onlinePlayers = $database->getOnlinePlayers(0);
			$this->assertTrue(is_array($onlinePlayers));
			$this->assertEqual(count($onlinePlayers), 3);
			echo "<pre>"; print_r($onlinePlayers); echo "</pre>";

			$onlinePlayers = $database->getOnlinePlayers(1);
			$this->assertTrue(is_array($onlinePlayers));
			$this->assertEqual(count($onlinePlayers), 1);
			$this->assertEqual($onlinePlayers[0]["username"], "josh4");

			sleep(2); // Should allow user josh1 to leave being online
//t=9 (8 is limit)			
			$onlinePlayers = $database->getOnlinePlayers(0);
			$this->assertTrue(is_array($onlinePlayers));
			$this->assertEqual(count($onlinePlayers), 3);

			sleep(SiteConfig::HEARTBEAT_DELAY_UNTIL_OFFLINE+6);
			$onlinePlayers = $database->getOnlinePlayers(0);
			$this->assertTrue(is_array($onlinePlayers));
			$this->assertEqual(count($onlinePlayers), 0);																																																																																																																																																																																																																																																																																															

			$this->assertTrue(!$database->isUserHere($josh4["id"], 1));
			$this->assertTrue(!$database->isUserHere($josh3["id"], 0));
			$this->assertTrue(!$database->isUserHere($josh4["id"], 0));
			$this->assertTrue(!$database->isUserHere($josh3["id"], 1));


		}
	
	}

?>
