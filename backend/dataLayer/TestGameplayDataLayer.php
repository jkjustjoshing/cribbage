<?php

	require_once("../../frontend/config.php");

	require_once(dirname(__FILE__) . "/../simpletest/autorun.php");
	require_once(dirname(__FILE__) . "/../dataLayer/DataLayer.class.php");
	require_once(dirname(__FILE__) . "/ResetDatabase.php");

	
	class TestGameplayDataLayer extends UnitTestCase{
		function start(){
			dropAllTables();
			addTables();


			$playerDatabase = DataLayer::getPlayerInstance();

			$playerDatabase->addPlayer("josh1", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh2", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh3", "josh1", "foo@foo.foo");
		}

		function testCreateGame(){
			echo "testCreateGame()<br />\n";

			$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();
			$database = DataLayer::getGameplayInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");
			$player3 = $playerDatabase->getPlayer("josh3");


			$gameID1 = $database->getGameID($player1["id"], $player2["id"]);
			$gameID1_1 = $database->getGameID($player2["id"], $player1["id"]);
			
			$gameID2 = $database->getGameID($player2["id"], $player3["id"]);
			$gameID2_1 = $database->getGameID($player3["id"], $player2["id"]);
			
			$gameID3 = $database->getGameID($player3["id"], $player1["id"]);
			$gameID3_1 = $database->getGameID($player1["id"], $player3["id"]);
			

			$this->assertEqual($gameID1, $gameID1_1);
			$this->assertEqual($gameID2, $gameID2_1);
			$this->assertEqual($gameID3, $gameID3_1);

			$this->assertNotEqual($gameID1, $gameID2);
			$this->assertNotEqual($gameID2, $gameID3);
			$this->assertNotEqual($gameID3, $gameID1);

			$info1 = $database->getGameInfo($gameID1);
			$this->assertEqual($info1["gameID"], $gameID1);
			$this->assertEqual($info1["player1ID"], $player1["id"]);
			$this->assertEqual($info1["player2ID"], $player2["id"]);
			$this->assertEqual($info1["player1Score"], 0);
			$this->assertEqual($info1["player2Score"], 0);
			$this->assertEqual($info1["turnID"], $player2["id"]);
			$this->assertEqual($info1["dealerID"], $player1["id"]);
			$this->assertEqual($info1["gamestatus"], "IN_PROGRESS");
			$this->assertEqual($info1["gamestate"], "DEALING");

			$info2 = $database->getGameInfo($gameID2);
			$this->assertEqual($info2["gameID"], $gameID2);

			$info3 = $database->getGameInfo($gameID3);
			$this->assertEqual($info3["gameID"], $gameID3);

		}

		function testChangeGameState(){
			echo "testChangeGameState()<br />\n";

			$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();
			$database = DataLayer::getGameplayInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");
			$player3 = $playerDatabase->getPlayer("josh3");

			$gameID = $database->getGameID($player1["id"], $player2["id"]);

			$database->changeGameState($gameID, "CHOOSING_CRIB");
			$this->assertEqual($database->getGameInfo($gameID)["gamestate"], "CHOOSING_CRIB");
			
			$database->changeGameState($gameID, "CUTTING_CARD");
			$this->assertEqual($database->getGameInfo($gameID)["gamestate"], "CUTTING_CARD");
			
			$database->changeGameState($gameID, "PEGGING");
			$this->assertEqual($database->getGameInfo($gameID)["gamestate"], "PEGGING");
			
			$database->changeGameState($gameID, "VIEWING_HANDS");
			$this->assertEqual($database->getGameInfo($gameID)["gamestate"], "VIEWING_HANDS");
			
			$database->changeGameState($gameID, "WAITING_PLAYER_1");
			$this->assertEqual($database->getGameInfo($gameID)["gamestate"], "WAITING_PLAYER_1");
			
			$database->changeGameState($gameID, "WAITING_PLAYER_2");
			$this->assertEqual($database->getGameInfo($gameID)["gamestate"], "WAITING_PLAYER_2");
			
			$database->changeGameState($gameID, "DEALING");
			$this->assertEqual($database->getGameInfo($gameID)["gamestate"], "DEALING");
			
		}

		function testPlayerHands(){
			echo "testPlayerHands()<br />\n";

			$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();
			$database = DataLayer::getGameplayInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");
			$player3 = $playerDatabase->getPlayer("josh3");

			$gameID = $database->getGameID($player1["id"], $player2["id"]);

			$cards = array(
				array("number"=>4,
					"suit"=>"spade",
					"inHand"=>0),
				array("number"=>11,
					"suit"=>"heart",
					"inHand"=>0),
				array("number"=>2,
					"suit"=>"diamond",
					"inHand"=>0),
				array("number"=>3,
					"suit"=>"spade",
					"inHand"=>0)
				);

			$database->writePlayerHand($gameID, $player1["id"], $cards);

			$dbHands = $database->getHands($gameID);
			$player1Hand = $dbHands[$player1["id"]];
			$this->assertEqual(count($cards), count($player1Hand));
			$this->assertFalse(array_key_exists($player2["id"], $dbHands));
			$this->assertFalse(array_key_exists("crib", $dbHands));

			$cribCards = array(
				array("number"=>2,
					"suit"=>"spade",
					"inHand"=>0),
				array("number"=>10,
					"suit"=>"heart",
					"inHand"=>0),
				array("number"=>12,
					"suit"=>"diamond",
					"inHand"=>0),
				array("number"=>1,
					"suit"=>"club",
					"inHand"=>0)
				);

			$database->putInCrib($gameID, $cards);

			$dbHands = $database->getHands($gameID);
			$crib = $dbHands["crib"];
			$this->assertEqual(count($cards), count($player1Hand));
			$this->assertFalse(array_key_exists($player2["id"], $dbHands));
			$this->assertEqual(count($cribCards), count($dbHands["crib"]));


			$twoCards = array(
				array("number"=>10,
					"suit"=>"heart",
					"inHand"=>0),
				array("number"=>12,
					"suit"=>"diamond",
					"inHand"=>0),
				array("number"=>1,
					"suit"=>"club",
					"inHand"=>0)
				);

			$database->writePlayerHand($gameID, $player2["id"],$twoCards);

			$dbHands = $database->getHands($gameID);
			$crib = $dbHands["crib"];
			$this->assertEqual(count($cards), count($player1Hand));
			$this->assertEqual(count($twoCards), count($dbHands[$player2["id"]]));
			$this->assertEqual(count($cribCards), count($dbHands["crib"]));

		}

		function testDeck(){
			echo "testDeck()<br />\n";

			$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();
			$database = DataLayer::getGameplayInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");

			$gameID = $database->getGameID($player1["id"], $player2["id"]);

			
		}

	}

?>
