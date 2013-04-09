<?php

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
			
			if($playerArr == false){
				$this->assertTrue(false);
			}
			
			$this->assertEqual($playerArr["username"], $username);
			$this->assertEqual($playerArr["email"], $email);
			
			$id = $playerArr["id"];
			
			$newPlayerArr = $database->getPlayer($id);
			$this->assertEqual($newPlayerArr["username"], $username);
			$this->assertEqual($newPlayerArr["email"], $email);
			
			
		}

	}

?>
