<?php

	require_once("../simpletest/autorun.php");
	require_once("PlayingCard.class.php");
	
	class TestPlayingCard extends UnitTestCase{
		function testConstructCardValidNumberInput(){
			echo "testConstructCardValidNumberInput()<br />";
			$card = new PlayingCard(1, "diamond");
			$this->assertNotNull($card);
			$card = new PlayingCard(2, "club");
			$this->assertNotNull($card);
			$card = new PlayingCard(3, "heart");
			$this->assertNotNull($card);
			$card = new PlayingCard(4, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(5, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(6, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(7, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(8, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(9, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(10, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(11, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(12, "spade");
			$this->assertNotNull($card);
			$card = new PlayingCard(13, "spade");
			$this->assertNotNull($card);
		}

		function testCardIntValueOutOfBounds(){
			echo "testCardIntValueOutOfBounds()<br />";
			$card = null;

			try{
				$card = new PlayingCard(0, "diamond");
				$this->assertTrue(false, "Exception should be thrown");
			}catch(Exception $e){
				$this->assertNull($card);
			}

			try{
				$card = new PlayingCard(-1, "heart");
				$this->assertTrue(false, "Exception should be thrown");
			}catch(Exception $e){
				$this->assertNull($card);
			}
			
			try{
				$card = new PlayingCard(14, "spade");
				$this->assertTrue(false, "Exception should be thrown");
            }catch(Exception $e){
                $this->assertNull($card);
            }	
				
			try{
				$card = new PlayingCard(15, "diamond");
				$this->assertTrue(false, "Exception should be thrown");
			}catch(Exception $e){
				$this->assertNull($card);
			}
		}

		function testInvalidSuitName(){
			echo "testInvalidSuitName()<br />";

			$card = null;

			$card = new PlayingCard(2, "spade");
			$this->assertNotNull($card);

			try{
				$card = null;
				$card = new PlayingCard(2, "josh");
			}catch(Exception $e){
				$this->assertNull($card);
			}
		}

		function testValidNumberStringForNumber(){
			echo "testValidNumberStringForNumber()<br />";
			
			$card = new PlayingCard("1", "diamond");
            $this->assertNotNull($card);
            $card = new PlayingCard("2", "club");
            $this->assertNotNull($card);
            $card = new PlayingCard("3", "heart");
            $this->assertNotNull($card);
            $card = new PlayingCard("4", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("5", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("6", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("7", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("8", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("9", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("10", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("11", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("12", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("13", "spade");
            $this->assertNotNull($card);
		}
		
		function testValidJQKStringForNumber(){	
			echo "testValidJQKStringForNumber()<br />";
			$card = new PlayingCard("J", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("Q", "spade");
            $this->assertNotNull($card);
            $card = new PlayingCard("K", "spade");
            $this->assertNotNull($card);
		}

		function testInvalidStringForNumber(){
			echo "testInvalidStringForNumber()<br />";

			$card = null;

			try{
				$card = new PlayingCard("-1", "spade");
				$this->assertTrue(false, "Should have been exception thrown");
			}catch(Exception $e){
				$this->assertNull($card);
			}

			try{
                $card = new PlayingCard("0", "spade");
                $this->assertTrue(false, "Should have been exception thrown");
            }catch(Exception $e){
                $this->assertNull($card);
            }

			try{
                $card = new PlayingCard("14", "spade");
                $this->assertTrue(false, "Should have been exception thrown");
            }catch(Exception $e){
                $this->assertNull($card);
            }

			try{
                $card = new PlayingCard("1 foo", "spade");
                $this->assertTrue(false, "Should have been exception thrown");
            }catch(Exception $e){
                $this->assertNull($card);
            }

			try{
                $card = new PlayingCard("foo 1", "spade");
                $this->assertTrue(false, "Should have been exception thrown");
            }catch(Exception $e){
                $this->assertNull($card);
            }

			try{
				$card = new PlayingCard(array(), "spade");
				$this->assertTrue(false, "Should have been exception thrown");
			}catch(Exception $e){
				$this->assertNull($card);
			}
	
			try{
				$card = new PlayingCard(array(1), "spade");
				$this->assertTrue(false, "Should have been exception thrown");
			}catch(Exception $e){
				$this->assertNull($card);
			}
		
		}
		
		function testToString(){
			echo "testToString()<br />";

			$spade = new PlayingCard(3, "spade");
			$diamond = new PlayingCard(8, "diamond");
			$heart = new PlayingCard("K", "heart");
			$club = new PlayingCard(13, "club");

			$this->assertEqual($spade->toString(), "3 of spades");
			$this->assertEqual($diamond->toString(), "8 of diamonds");
			$this->assertEqual($heart->toString(), "K of hearts");
			$this->assertEqual($club->toString(), "K of clubs");

		}

		function testGetNumber(){
			echo "testGetNumber()<br />";
			
			$one = new PlayingCard(1, "diamond");
			$two = new PlayingCard(2, "spade");
			$king = new PlayingCard(13, "spade");
			$king2 = new PlayingCard("K", "spade");
			$queen = new PlayingCard("Q", "spade");
			$jack = new PlayingCard("J", "spade");

			$this->assertEqual($one->getNumber(), 1);
			$this->assertEqual($two->getNumber(), 2);
			$this->assertEqual($king->getNumber(), 13);
			$this->assertEqual($king2->getNumber(), 13);
			$this->assertEqual($queen->getNumber(), 12);
			$this->assertEqual($jack->getNumber(), 11);
		}

		function testGetSuit(){
			echo "testGetSuit()<br />";
			
			$spade = new PlayingCard(3, "spade");
            $diamond = new PlayingCard(8, "diamond");
            $heart = new PlayingCard("K", "heart");
            $club = new PlayingCard(13, "club");

            $this->assertEqual($spade->getSuit(), "spade");
            $this->assertEqual($diamond->getSuit(), "diamond");
            $this->assertEqual($heart->getSuit(), "heart");
            $this->assertEqual($club->getSuit(), "club");	

		}

	}

?>
