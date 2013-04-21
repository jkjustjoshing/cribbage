<?php

	require_once(dirname(__FILE__) . "/../dataLayer/DataLayer.class.php");

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
		
		public static function post($userID, $opponentID, $chatContent){
			DataLayer::postChat($userID, $opponentID, $chatContent);
		}
		
	}
	
	
	/**
	 * ChatRoom
	 * 
	 * Holds chats from one chat feed.
	 * Must put in chats in the right order.
	 */
	class ChatRoom implements Iterator{
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
		
		private function __construct($userID, $opponentID){
			$this->userID = $userID;
			$this->opponentID = $opponentID;
		}
		
		// Iterator code taken from http://php.net/manual/en/language.oop5.iterations.php
		public function rewind(){
			reset($this->chatItems);
		}
		public function current(){
			$var = current($this->chatItems);
			return $var;
		}
		public function key(){
			$var = key($this->chatItems);
			return $var;
		}
		public function next(){
			$var = next($this->chatItems);
			return $var;
		}
		public function valid(){
			$key = key($this->chatItems);
			$var = ($key !== NULL && $key !== FALSE);
			return $var;
		}
		// end iterator code taken from http://php.net/manual/en/language.oop5.iterations.php
		
		public static function getChatRoom($userID, $opponentID, $lastSeenTimestamp = null){
			$room = new ChatRoom($userID, $opponentID);
			
			$chatArr = DataLayer::getChats($userID, $opponentID, $lastSeenTimestamp);
			
			foreach($chatArr as $chat){
				$chatItem = new ChatItem($chat["poster"], $chat["content"], $chat["timestamp"]);
				
				$room->addItem($chatItem);
			}
			
			return $room;
			
		}
		
		
	}
	


?>
