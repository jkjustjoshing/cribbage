<?php

	require_once("../simpletest/autorun.php");
	require_once("Chat.class.php");
	
	class TestChat extends UnitTestCase{
		
		function testChatItemEquals(){
			echo "testChatItemEquals()<br />";
			
			//$posterID, $chatContent, $timestamp
			$chatItem = new ChatItem(1, "hello, this is dog", time());
			$chatItem2 = new ChatItem(1, "hello, this is dog", time());
			$chatItem3 = new ChatItem(3, "hello, this is dog", time());
			$chatItem4 = new ChatItem(1, "hello, this is NOT dog", time());
			$chatItem5 = new ChatItem(1, "hello, this is dog", time() - 1);
			
			$this->assertTrue($chatItem->equals($chatItem2));
			$this->assertFalse($chatItem->equals($chatItem3));
			$this->assertFalse($chatItem->equals($chatItem4));
			$this->assertFalse($chatItem->equals($chatItem5));
			$this->assertFalse($chatItem->equals(null));
			$this->assertFalse($chatItem->equals(new ChatRoom()));
		
		}

		function testChatRoom(){
		
		}

	}

?>
