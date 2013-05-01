<?php

	require_once("../../frontend/config.php");

	require_once(dirname(__FILE__) . "/../simpletest/autorun.php");
	require_once(dirname(__FILE__) . "/DataLayer.class.php");
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
	
	}

?>
