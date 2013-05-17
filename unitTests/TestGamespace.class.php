<?php

	require_once("simpletest/autorun.php");
	require_once("../frontend/config.php");
	require_once(BACKEND_DIRECTORY . "/../unitTests/ResetDatabase.php");
	require_once(BACKEND_DIRECTORY . "/../backend/businessLayer/gameplay/CardDeck.class.php");
	require_once(BACKEND_DIRECTORY . "/../backend/businessLayer/gameplay/Gamespace.class.php");
	require_once(BACKEND_DIRECTORY . "/../backend/businessLayer/Player.class.php");
	require_once(BACKEND_DIRECTORY . "/../backend/dataLayer/DataLayer.class.php");

	
	class TestGamespace extends UnitTestCase{
		
		function start(){
			dropAllTables();
			addTables();

			$playerDatabase = DataLayer::getPlayerInstance();

			$playerDatabase->addPlayer("josh1", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh2", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh3", "josh1", "foo@foo.foo");

		}

		function testDatabase(){
			echo "testDatabase()<br />\n";
			$this->start();

			$database = DataLayer::getGameplayInstance();

			$gameID = $database->getGameID(1,2);
			$this->assertTrue($gameID);

			$gameID2 = $database->getGameID(1,2);
			$this->assertEqual($gameID, $gameID2);

			$gameID3 = $database->getGameID(2,1);
			$this->assertEqual($gameID, $gameID3);

		}

		function testCreate(){
			echo "testCreate()<br />\n";

			$gameID = Gamespace::getGameID((new Player("josh1"))->id, (new Player("josh2"))->id);
			$this->assertTrue($gameID);
			$player = new Player("josh1");
			$gamespace = new Gamespace($gameID, 1);
			$this->assertEqual($gamespace->gamestate, "DEALING");

		}

		function testDeal(){
			echo "testDeal()<br />\n";

			$playerDatabase = DataLayer::getPlayerInstance();

			$playerDatabase->addPlayer("josh1", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh2", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh3", "josh1", "foo@foo.foo");

			$josh1 = $playerDatabase->getPlayer("josh1");
			$josh2 = $playerDatabase->getPlayer("josh2");
			$josh3 = $playerDatabase->getPlayer("josh3");

			$gameID = Gamespace::getGameID($josh1["id"], $josh2["id"]);
			$gamespace = new Gamespace($gameID, $josh1["id"]);
			$this->assertEqual($gamespace->gamestate, "DEALING");

			$gamespace->deal(5); //shuffle 5 times
			
			$this->assertEqual($gamespace->gamestate, "CHOOSING_CRIB");

			// Get my hand
			$myHand = $gamespace->getMyHand();
			$myCards = $myHand->getCards();
			$this->assertEqual(count($myCards), 6);
			foreach($myCards as $card){
				$this->assertFalse($card["card"]->equals(new PlayingCard(0, null)));
			}
			
			// Get opponent's hand
			$opponentHand = $gamespace->getOpponentHand();
			$opponentCards = $opponentHand->getCards();
			$this->assertEqual(count($opponentCards), 6);
			foreach($opponentCards as $card){
				$this->assertTrue($card["card"]->equals(new PlayingCard(0, null)));
			}

			// Choose 2 cards for crib
	 		$gamespace->putCardsInCrib($myHand->getCards()[0]["card"], $myHand->getCards()[1]["card"]); 
			// Make sure the hand has 4 cards and the crib has 2
			$myHand = $gamespace->getMyHand();
			$cardCount = $myHand->numberOfCardsInHand();
			print_r($myHand->getCards());
			$this->assertEqual($cardCount, 4);
			$crib = $gamespace->getCrib();
			$cribCards = $crib->getCards();
			$this->assertEqual(count($cribCards), 2);


			// Get gamespace from other player's side
			// Choose 2 cards for crib
			// Test gamestate changed to pegging

		}

	}

?>