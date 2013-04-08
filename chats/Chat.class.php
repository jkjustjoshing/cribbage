<?php

	/**
	 * ChatItem
	 *
	 * An entry in a chat.
	 */
	class ChatItem{
		
		public $chatContent;
		public $posterID;
		public $timestamp;
		
		public function __construct($posterID, $chatContent, $timestamp){
			$this->chatContent = $chatContent;
			$this->posterID = $posterID;
			$this->timestamp = $timestamp;
		}
		
		public function equals($other){
			if(!is_object($other)){
				return false;
			}
			if(get_class($other) != get_class($this)){
				return false;
			}
			
			return 
				$this->chatContent == $other->chatContent &&
				$this->posterID == $other->posterID &&
				$this->timestamp == $other->timestamp;
		
		}
	}
	
	
	/**
	 * ChatRoom
	 * 
	 * Holds chats from one chat feed.
	 * Must put in chats in the right order.
	 */
	class ChatRoom{
		private $chatItems = array();
		private $userID;
		private $opponentID;
		
		/**
		 * addItem
		 *
		 * Adds a chat item to the chat log. Inserts at end, NOT in any order
		 * @throws InvalidArgumentException if passed variable is not a ChatItem object
		 */
		public function addItem($chatItem){
			if(!is_object($chatItem) || get_class($chatItem) !== "ChatItem"){
				throw new InvalidArgumentException("Must only add ChatItem objects");
			}
			
			$chatItems[] = $chatItem;
			
		}
		
		
	}
	


?>
