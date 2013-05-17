<?php

	require_once("simpletest/autorun.php");
	require_once("../frontend/config.php");
	require_once(BACKEND_DIRECTORY . "/../unitTests/ResetDatabase.php");
	require_once(BACKEND_DIRECTORY . "/../backend/businessLayer/gameplay/CardDeck.class.php");
	require_once(BACKEND_DIRECTORY . "/../backend/businessLayer/gameplay/Gamespace.class.php");
	require_once(BACKEND_DIRECTORY . "/../backend/businessLayer/Player.class.php");
	
	class TestCardDeck extends UnitTestCase{
		
		function start(){
			dropAllTables();
			addTables();
		}

		function testConstructAndPop(){
			echo "testConstructAndPop()<br />";
			$this->start();

			$deck = CardDeck::getDeck(1, false);
			$this->assertEqual($deck->size(), 52);
			
			// Make sure they are in order
			$number = 0;
			$suit = "";
			$left = 52;
			for($card = $deck->pop(); $card !== null; $card = $deck->pop()){
				$this->assertEqual(--$left, $deck->size());
				$this->assertEqual($number, $card->getNumber()%13);
				if($number != 0) $this->assertEqual($suit, $card->getSuit());
				else $suit = $card->getSuit();
				$number = ($number+12) % 13;
			}


			$deck = CardDeck::getDeck(2, CardDeck::SHUFFLE);
			$this->assertEqual($deck->size(), 52);
			
			// Make sure the first 2 cards aren't as above (at least 1 will pass 99.84% of the time if done properly)
			$card = $deck->pop();
			$test1 = $card->getNumber() !=  13;
			$suit = $card->getSuit();

			$card = $deck->pop();
			$test2 = $card->getNumber() != 12;
			$test3 = $card->getSuit() != $suit;

			$this->assertTrue($test1 || $test2 || $test3);

		}

		function testDatabaseDeck(){
			echo "testDatabaseDeck()<br />\n";

			$this->start();

			$deck = CardDeck::getDeck(3);
			unset($deck);

			$dbDeck = CardDeck::getDeck(3);
			$this->assertEqual($dbDeck->size(), 52);
			$dbDeck->shuffle(3);
			$dbDeck->pop();
			$card20 = $dbDeck->getCards("This is part of a unit test.")[20];
			unset($dbDeck);

			$newDbDeck = CardDeck::getDeck(3);

			// Is it the same deck?
			$this->assertEqual($newDbDeck->size(), 51);
			$this->assertEqual($card20->getNumber(), $newDbDeck->getCards("This is part of a unit test.")[20]->getNumber());
			$this->assertEqual($card20->getSuit(), $newDbDeck->getCards("This is part of a unit test.")[20]->getSuit());

			//Shouldn't be able to shuffle a not-full deck
			try{
				$newDbDeck->shuffle(3);
				$exception = false;
			}catch(Exception $e){
				$exception = true;
			}

			$this->assertTrue(isset($exception));
			$this->assertTrue($exception);

			$newDbDeck->resetDeck();
			$this->assertEqual($newDbDeck->size(), 52);

		}

	}

?>