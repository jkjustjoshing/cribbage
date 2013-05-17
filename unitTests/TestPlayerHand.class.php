<?php

	require_once("simpletest/autorun.php");
	require_once("../backend/businessLayer/gameplay/PlayerHand.class.php");
	
	class TestPlayerHand extends UnitTestCase{
		function testAddingCardSuccessfully(){
			echo "testAddingCardSuccessfully()<br />";

			$hand = new PlayerHand();
			$this->assertEqual($hand->numberOfCardsInHand(), 0);

			
			for($i = 1; $i < 5; ++$i){
				$card1 = new PlayingCard($i, "heart");
				$hand->add($card1);
				$this->assertEqual($hand->numberOfCardsInHand(), $i);
			}

		}

		function testAddingCardInvalid(){
			echo "testAddingCardInvalid()<br />";

			$hand = new PlayerHand();
			
			try{
				$hand->add(1);
				$this->assertTrue(false, "Shouldn't reach here");
			}catch(Exception $e){
				$this->assertEqual($hand->numberOfCardsInHand(), 0);
			}

			try{
                $hand->add(array());
                $this->assertTrue(false, "Shouldn't reach here");
            }catch(Exception $e){
                $this->assertEqual($hand->numberOfCardsInHand(), 0);
            }

			try{
                $hand->add(new PlayerHand());
                $this->assertTrue(false, "Shouldn't reach here");
            }catch(Exception $e){
                $this->assertEqual($hand->numberOfCardsInHand(), 0);
            }

			try{
                $hand->add("foo");
                $this->assertTrue(false, "Shouldn't reach here");
            }catch(Exception $e){
                $this->assertEqual($hand->numberOfCardsInHand(), 0);
            }

		}
	
		function testGetCards(){
			echo "testGetCards()<br />";

			$cardArr = array();
			$hand = new PlayerHand();

			$card1 = new PlayingCard(4, "club");
			$cardArr[] = $card1;
			$hand->add($card1);
			
			$card2 = new PlayingCard(5, "heart");
			$cardArr[] = $card2;
			$hand->add($card2);

			$card3 = new PlayingCard(5, "diamond");
			$cardArr[] = $card3;
			$hand->add($card3);

			$card4 = new PlayingCard(6, "diamond");
			$cardArr[] = $card4;
			$hand->add($card4);
			
			
			//Compare $hand->getCards() with $cardArr
			$handArr = $hand->getCards();
			for($i = 0; $i < count($handArr); ++$i){
				$this->assertTrue($handArr[$i]->equals($cardArr[$i]));
			}
		}

		function testCardFlushes(){
			echo "testCardFlushes()<br />";

			// Crib only allow 5 card flush
			$crib = new PlayerHand(PlayerHand::CRIB);
            $suit = "club";
            $otherSuit = "spade";
            $crib->add(new PlayingCard(2, $suit));
            $crib->add(new PlayingCard(4, $suit));
            $crib->add(new PlayingCard(6, $suit));
            $crib->add(new PlayingCard(8, $suit));
            	$score = $crib->totalPoints(new PlayingCard(10, $suit));
            	$this->assertEqual($score, 5);
            	
				$score = $crib->totalPoints(new PlayingCard(10, $otherSuit));
            	$this->assertEqual($score, 0);
				
				$crib->remove(new PlayingCard(2, $suit));
				$crib->add(new PlayingCard(2, $otherSuit));
				$score = $crib->totalPoints(new PlayingCard(10, $suit));
				$this->assertEqual($score, 0);
			
			// Hand can have 4 or 5 card flush, but only if all 4 are in hand
			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
			$suit = "club";
			$otherSuit = "spade";
			$hand->add(new PlayingCard(2, $suit));
			$hand->add(new PlayingCard(4, $suit));
			$hand->add(new PlayingCard(6, $suit));
			$hand->add(new PlayingCard(8, $suit));
				$score = $hand->totalPoints(new PlayingCard(10, $suit));
				$this->assertEqual($score, 5);
				
				$score = $hand->totalPoints(new PlayingCard(10, $otherSuit));
				$this->assertEqual($score, 4);
				
				$hand->remove(new PlayingCard(2, $suit));
                $hand->add(new PlayingCard(2, $otherSuit));
                $score = $hand->totalPoints(new PlayingCard(10, $suit));
                $this->assertEqual($score, 0);
		}

		function testCardFifteens(){
			echo "testCardFifteens()<br />";	
			
			$hand = new PlayerHand(PlayerHand::NOT_CRIB);

			$hand->add(new PlayingCard(5, "club"));
			$hand->add(new PlayingCard(10, "heart"));
			$hand->add(new PlayingCard(3, "diamond"));
			$hand->add(new PlayingCard(2, "diamond"));
			$score = $hand->totalPoints(new PlayingCard(12, "diamond"));
			$this->assertEqual($score, 8);
	
			$hand = new PlayerHand(PlayerHand::NOT_CRIB);

            $hand->add(new PlayingCard(7, "club"));
            $hand->add(new PlayingCard(8, "heart"));
            $hand->add(new PlayingCard(5, "diamond"));
            $hand->add(new PlayingCard(10, "diamond"));
            $score = $hand->totalPoints(new PlayingCard(2, "diamond"));
            $this->assertEqual($score, 6);


		}

		function testCardPairs(){
			echo "testCardPairs()<br />";

			$hand = new PlayerHand(PlayerHand::NOT_CRIB);

            $hand->add(new PlayingCard(5, "club"));
            $hand->add(new PlayingCard(5, "heart"));
            $hand->add(new PlayingCard(3, "diamond"));
            $hand->add(new PlayingCard(3, "spade"));
            $score = $hand->totalPoints(new PlayingCard(6, "diamond"));
            $this->assertEqual($score, 4);


			$hand = new PlayerHand(PlayerHand::NOT_CRIB);

            $hand->add(new PlayingCard(3, "club"));
            $hand->add(new PlayingCard(3, "heart"));
            $hand->add(new PlayingCard(3, "diamond"));
            $hand->add(new PlayingCard(7, "spade"));
            $score = $hand->totalPoints(new PlayingCard(7, "diamond"));
            $this->assertEqual($score, 8);

		}
		

		function testCardRun(){
			echo "testCardRun()<br />";

			// 3 card run
			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard(11, "club"));
            $hand->add(new PlayingCard(12, "heart"));
            $hand->add(new PlayingCard(13, "diamond"));
            $hand->add(new PlayingCard(2, "diamond"));
            $score = $hand->totalPoints(new PlayingCard(4, "diamond"));
            $this->assertEqual($score, 3);


			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard(10, "club"));
            $hand->add(new PlayingCard(12, "heart"));
            $hand->add(new PlayingCard(11, "diamond"));
            $hand->add(new PlayingCard(13, "diamond"));
            $score = $hand->totalPoints(new PlayingCard(8, "spade"));
            $this->assertEqual($score, 4);


			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard(12, "club"));
            $hand->add(new PlayingCard(10, "heart"));
            $hand->add(new PlayingCard(11, "diamond"));
            $hand->add(new PlayingCard(13, "diamond"));
            $score = $hand->totalPoints(new PlayingCard(9, "spade"));
            $this->assertEqual($score, 5);	
		}

		function testDoubleRun(){
			echo "testDoubleRun()<br />";

			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard(10, "club"));
            $hand->add(new PlayingCard(12, "heart"));
            $hand->add(new PlayingCard(11, "diamond"));
            $hand->add(new PlayingCard(13, "diamond"));
            $score = $hand->totalPoints(new PlayingCard(10, "spade"));
            $this->assertEqual($score, 10);


            $hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard(5, "club"));
            $hand->add(new PlayingCard(4, "heart"));
            $hand->add(new PlayingCard(4, "diamond"));
            $hand->add(new PlayingCard(3, "diamond"));
            $score = $hand->totalPoints(new PlayingCard(1, "spade"));
            $this->assertEqual($score, 8);

		}

		function testFlushStraightWithKnobbs(){
			echo "testFlushStraightWithKnobbs()<br />";

			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard(9, "diamond"));
            $hand->add(new PlayingCard(10, "diamond"));
            $hand->add(new PlayingCard("J", "diamond"));
            $hand->add(new PlayingCard("Q", "diamond"));
            $score = $hand->totalPoints(new PlayingCard("K", "diamond"));
            $this->assertEqual($score, 11);	
		}

		function testComplexHands(){
			echo "testComplexHands()<br />";

			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard(5, "club"));
            $hand->add(new PlayingCard(4, "heart"));
            $hand->add(new PlayingCard(5, "diamond"));
            $hand->add(new PlayingCard(6, "diamond"));
            $score = $hand->totalPoints(new PlayingCard(6, "spade"));
            $this->assertEqual($score, 24);

			$hand = new PlayerHand(PlayerHand::NOT_CRIB);
            $hand->add(new PlayingCard("J", "club"));
            $hand->add(new PlayingCard("Q", "heart"));
            $hand->add(new PlayingCard("K", "diamond"));
            $hand->add(new PlayingCard("5", "diamond"));
            $score = $hand->totalPoints(new PlayingCard(5, "club"));
            $this->assertEqual($score, 18);

		}
	}

?>
