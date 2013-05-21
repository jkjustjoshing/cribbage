<?php

	require_once("../../frontend/config.php");
	require_once("../businessLayer/gameplay/Gamespace.class.php");
	require_once("../simpletest/autorun.php");
	require_once("ResetDatabase.php");

	class TestSecurityToken extends UnitTestCase{

		function start(){
			dropAllTables();
			addTables();

			$playerDatabase = DataLayer::getPlayerInstance();

			$playerDatabase->addPlayer("josh1", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh2", "josh1", "foo@foo.foo");
			$playerDatabase->addPlayer("josh3", "josh1", "foo@foo.foo");
		}

		function testGamespace(){
			//$this->start();

			$playerDatabase = DataLayer::getPlayerInstance();

			$player1 = $playerDatabase->getPlayer("josh1");
			$player2 = $playerDatabase->getPlayer("josh2");

			$gameID = Gamespace::getGameID($player1["id"], $player2["id"]);

			$gamespace = new Gamespace($gameID, $player1["id"]);

			$gamespace->deal(4);

			$myHand = $gamespace->getMyHand();

			$gamespace->putCardsInCrib($myHand->getCards()[0]["card"], $myHand->getCards()[1]["card"]);
unset($gamespace);
			$gamespace = new Gamespace($gameID, $player1["id"]);

			print_r($gamespace->getMyHand());

			$this->assertEqual(4, count($gamespace->getMyHand()->getCards()));
		}

	}


?>
