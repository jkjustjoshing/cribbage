<?php

	require_once("../simpletest/autorun.php");
	require_once("PlayerHand.class.php");
	
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

		function testCardSinglePair(){
			echo "testCardSinglePair()<br />";

			$hand = new PlayerHand();

			$hand->add(new PlayingCard(5, "club"));
			$hand->add(new PlayingCard(5, "spade"));
			$hand->add(new PlayingCard(3, "heart"));
			$hand->add(new PlayingCard(6, "diamond"));

			$score = $hand->totalPoints(new PlayingCard(8, "diamond"));
			$this->assertEqual($score, 2, "Score should equal 2");

		}

	}

?>
