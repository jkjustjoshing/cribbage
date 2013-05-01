<?php

	require_once(BACKEND_DIRECTORY . "/SiteConfig.class.php");

	class DataLayer{
		
		private $mysqli;
		private static $instance;
		private static $chatInstance;
		private static $playerInstance;
		private static $challengeInstance;
		private static $gameplayInstance;
		private static $instanceCount = 0;

		

		private function __construct($db_host, $db_username, $db_password, $db_database){
			$this->mysqli = new mysqli($db_host, $db_username, $db_password, $db_database);

			if($this->mysqli->connect_errno > 0){
				throw new RuntimeException("Could not connect to database");
			}

		}
		
		public static function getInstance(){
			self::$instanceCount++;
			if(self::$instance === null){
				self::$instance = new DataLayer(
							SiteConfig::DATABASE_SERVER,
							SiteConfig::DATABASE_USER,
							SiteConfig::DATABASE_PASSWORD,
							SiteConfig::DATABASE_DATABASE
						);
			}
			return self::$instance;
		}
		
		public static function getChatInstance(){
			if(self::$chatInstance === null){
				$instance = self::getInstance();
				self::$chatInstance = new ChatDataLayer($instance->mysqli);
			}
			return self::$chatInstance;
		}
		
		public static function getPlayerInstance(){
			if(self::$playerInstance === null){
				$instance = self::getInstance();
				self::$playerInstance = new PlayerDataLayer($instance->mysqli);	
			}
			return self::$playerInstance;
		}
		
		public static function getChallengeInstance(){
			if(self::$challengeInstance === null){
				$instance = self::getInstance();
				self::$challengeInstance = new ChallengeDataLayer($instance->mysqli);	
			}
			return self::$challengeInstance;
		}
		
		public static function getGameplayInstance(){
			if(self::$gameplayInstance === null){
				$instance = self::getInstance();
				self::$gameplayInstance = new GameplayDataLayer($instance->mysqli);	
			}
			return self::$gameplayInstance;
		}
	
	
		public function __destruct(){
			self::$instanceCount--;
			if(self::$instanceCount == 0){
				$this->mysqli->close();
			}
		}

	}


	class DatabaseException extends Exception{
		public function __construct($message, $code = 0, Exception $previous = null){
			parent::__construct($message, $code, $previous);
		}
		
		public function __toString(){
			return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
		}
	}

	require_once(dirname(__FILE__) . "/ChatDataLayer.class.php");
	require_once(dirname(__FILE__) . "/PlayerDataLayer.class.php");
	require_once(dirname(__FILE__) . "/ChallengeDataLayer.class.php");
	require_once(dirname(__FILE__) . "/GameplayDataLayer.class.php");

?>
