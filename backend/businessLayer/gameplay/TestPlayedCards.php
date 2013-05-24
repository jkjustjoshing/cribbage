<?php

	require_once("../../../frontend/config.php");
	require_once("../../simpletest/autorun.php");
	require_once("PlayedCards.class.php");
	require_once("Gamespace.class.php");
	require_once("../../dataLayer/ResetDatabase.php");
	
	class TestCardDeck extends UnitTestCase{
		
		function start(){
			dropAllTables();
			addTables();

			$playerDatabase = DataLayer::getPlayerInstance();

			$playerDatabase->addPlayer("josh1", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh2", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh3", "josh1", "foo@foo.foo");
		}


		function testPegging1(){
			echo "testPegging1()<br />\n";

			$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");

			$gameID = Gamespace::getGameID($player1["id"], $player2["id"]);

			$playedCards = new PlayedCards($gameID);

			$result = $playedCards->play((new PlayingCard(3, "club")), 1);
			$this->assertEqual($result, 0);
			
			$result = $playedCards->play((new PlayingCard(13, "club")), 2);
			$this->assertEqual($result, 0);
			
			$result = $playedCards->play((new PlayingCard(2, "club")), 1);
			$this->assertEqual($result, 2);
			
			unset($playedCards);
			$playedCards = new PlayedCards($gameID);

			$result = $playedCards->play((new PlayingCard(4, "club")), 2);
			$this->assertEqual($result, 0);
			
			$result = $playedCards->play((new PlayingCard(3, "club")), 1);
			$this->assertEqual($result, 3);
			
			$result = $playedCards->play((new PlayingCard(6, "club")), 2);
			$this->assertEqual($result, 0);
			
			$result = $playedCards->play((new PlayingCard(3, "club")), 1);
			$this->assertEqual($result, 2);

			$result = $playedCards->play(new PlayingCard(3, "club", 1), 1);
			$this->assertTrue($result === false);
			
			$this->assertEqual(count($playedCards->getScreenCards()), count($playedCards->getAllCards()));


		}

		function testStraights(){
			echo "testStraights()<br />\n";

			$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");

			$gameID = Gamespace::getGameID($player1["id"], $player2["id"]);

			$playedCards = new PlayedCards($gameID);

			$result = $playedCards->play((new PlayingCard(2, "heart")), 1);
			$this->assertEqual($result, 0);
			$this->assertEqual($playedCards->getCount(), 2);
			
			$result = $playedCards->play((new PlayingCard(3, "diamond")), 2);
			$this->assertEqual($result, 0);
			$this->assertEqual($playedCards->getCount(), 5);
			
			$result = $playedCards->play((new PlayingCard(4, "spade")), 1);
			$this->assertEqual($result, 3);
			$this->assertEqual($playedCards->getCount(), 9);
			
			$result = $playedCards->play((new PlayingCard(4, "club")), 2);
			$this->assertEqual($result, 2);
			$this->assertEqual($playedCards->getCount(), 13);
			
			$playedCards->clear();

			$result = $playedCards->play((new PlayingCard(3, "club")), 1);
			$this->assertEqual($result, 0);
			
			$result = $playedCards->play((new PlayingCard(2, "club")), 2);
			$this->assertEqual($result, 0);
			
			$result = $playedCards->play((new PlayingCard(5, "club")), 1);
			$this->assertEqual($result, 0);

			$result = $playedCards->play(new PlayingCard(4, "club", 1), 1);
			$this->assertEqual($result, 4);

			$result = $playedCards->play(new PlayingCard(6, "club", 1), 1);
			$this->assertEqual($result, 5);

			$result = $playedCards->play(new PlayingCard(1, "club", 1), 1);
			$this->assertEqual($result, 6);

			$result = $playedCards->play(new PlayingCard(12, "club", 1), 1);
			$this->assertEqual($result, 2);
			
			$this->assertNotEqual(count($playedCards->getScreenCards()), count($playedCards->getAllCards()));

		}

		function testMultipes(){
			echo "testMultiples()<br />\n";

			$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");

			$gameID = Gamespace::getGameID($player1["id"], $player2["id"]);

			$playedCards = new PlayedCards($gameID);

			$result = $playedCards->play((new PlayingCard(2, "heart")), 1);
			$this->assertEqual($result, 0);
			$this->assertEqual($playedCards->getCount(), 2);
			
			$result = $playedCards->play((new PlayingCard(3, "diamond")), 2);
			$this->assertEqual($result, 0);
			$this->assertEqual($playedCards->getCount(), 5);
			
			$result = $playedCards->play((new PlayingCard(4, "spade")), 1);
			$this->assertEqual($result, 3);
			$this->assertEqual($playedCards->getCount(), 9);

			unset($playedCards);
			$playedCards = new PlayedCards($gameID);

			$result = $playedCards->play((new PlayingCard(4, "spade")), 1);
			$this->assertEqual($result, 2);
			$this->assertEqual($playedCards->getCount(), 13);

			$result = $playedCards->play((new PlayingCard(4, "spade")), 1);
			$this->assertEqual($result, 6);
			$this->assertEqual($playedCards->getCount(), 17);

			$result = $playedCards->play((new PlayingCard(4, "spade")), 1);
			$this->assertEqual($result, 12);
			$this->assertEqual($playedCards->getCount(), 21);

			PlayedCards::clearDatabase($gameID);
			$playedCards = new PlayedCards($gameID);
			$this->assertEqual($playedCards->getCount(), 0);
			$this->assertEqual(count($playedCards->getAllCards()), 0);


		}

	}

?>