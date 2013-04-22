<?php

	require_once("../simpletest/autorun.php");
	require_once("CardDeck.class.php");
	
	class TestCardDeck extends UnitTestCase{
		
		function testConstructAndPop(){
			echo "testConstructAndPop()<br />";

			$deck = new CardDeck();
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


			$deck = new CardDeck(CardDeck::SHUFFLE);
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

		function testPickCutCard(){
			echo "testPickCutCard() - NOT YET IMPLEMENTED<br />";
		}

	}

?>