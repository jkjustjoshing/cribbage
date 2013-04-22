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
			
			$database = DataLayer::getInstance();
			$this->assertTrue(is_object($database));
			$this->assertEqual(get_class($database), "DataLayer");
			
			// Tests that only one instance is being created for everyone
			$this->assertTrue($database === DataLayer::getInstance());
			
		}

		function testAddPlayerAndCheckPassword(){
			echo "testAddPlayerAndCheckPassword()<br />";
			
			$this->start();
			
			$database = DataLayer::getInstance();
			
			// ->addPlayer($username, $password, $email);
			$database->addPlayer("player1", "password", "email@email.com");
			$this->assertTrue($database->checkPassword("player1", "password"));
			$this->assertFalse($database->checkPassword("player1", "password_not_same"));
			$this->assertFalse($database->checkPassword("player1", "password_also not same"));
			$this->assertTrue($database->checkPassword("player1", "password"));
			$database->addPlayer("player2", "password", "email@email.com");
			$this->assertTrue($database->checkPassword("player2", "password"));
			$this->assertFalse($database->checkPassword("player2", "password_not_same"));
			$this->assertFalse($database->checkPassword("player2", "password_also not same"));
			$this->assertTrue($database->checkPassword("player2", "password"));
			
			// By manual database inspection, the stored passwords for these 2 users are 
			// not the same, despite having identical passwords. This shows that hashing
			// is working.
			
		}
	
		function testGetPlayer(){
			echo "testGetPlayer()<br />";
			
			$this->start();
			
			$database = DataLayer::getInstance();
			
			$username = "player1";
			$password = "password";
			$email = "email@email.com";
			
			$database->addPlayer($username, $password, $email);
			
			$playerArr = $database->getPlayer($username);
			
			if($playerArr === false){
				$this->assertTrue(false);
				return;
			}
			
			$this->assertEqual($playerArr["username"], $username);
			$this->assertEqual($playerArr["email"], $email);
			
			$id = $playerArr["id"];
			
			$newPlayerArr = $database->getPlayer($id);
			$this->assertEqual($newPlayerArr["username"], $username);
			$this->assertEqual($newPlayerArr["email"], $email);
		
		}
		
		function testChats(){
			echo "testChats() - Incomplete without injection attempt<br />";
			
			$this->start();
			
			$database = DataLayer::getInstance();
			
			$database->addPlayer("josh", "josh", "josh@josh.josh");
			$database->addPlayer("sam", "sam", "josh@josh.josh");
			$database->addPlayer("max", "max", "josh@josh.josh");
			
			$joshArr = $database->getPlayer("josh");
			$samArr = $database->getPlayer("sam");
			
			$joshMessage = "this is josh. I think sam is an idiot";
			$samMessage = "this is sam. I know josh is an idiot";
			$database->postChat($joshArr["id"], $samArr["id"], $joshMessage);
			$database->postChat($samArr["id"], $joshArr["id"], $samMessage);
			
			
			$chats1 = $database->getChats($joshArr["id"], $samArr["id"]);
			
			$this->assertTrue($chats1[0]["poster"], $joshArr["id"]);
			$this->assertTrue($chats1[0]["content"], $joshMessage);
			
			$this->assertTrue($chats1[1]["poster"], $samArr["id"]);
			$this->assertTrue($chats1[1]["content"], $samMessage);			
			
		}

	}

?>
