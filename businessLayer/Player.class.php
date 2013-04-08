<?

	require_once(dirname(__FILE__) . "/../dataLayer/DataLayer.class.php");

	/**
	 * Player class
	 * @author Josh Kramer
	 * Models a player
	 *
	 */
	class Player{
		
		private $id = null;
		private $username = null;
		private $email = null;
		private $receiveNotifications = null;
		
		const SALT_LENGTH = 20;
		const USERNAME_WHITELIST = "/^[a-zA-Z0-9_]+$/";
		
		/**
		 * login($username, $password)
		 * 
		 * Checks login information
		 * @return false on failed login, Player object on success
		 */
		public static function login($username, $password){
			if(!preg_match(self::USERNAME_WHITELIST, $username)){
				// Username doesn't match whitelist.
				return false;
			}
			
			// Username is safe for database
			$database = DataLayer::getInstance();
			
			$userArray = $database->getUser($username);
			
			if($userArray === false){
				// Failure
				return false;
			}
			
			// Check password
			if(self::checkPassword($userArray["password"], $password)){
				
				// Password is correct
				// Setup Player object, set security token
				$player = new Player();
				$player->id = $userArray["id"];
				$player->username = $userArray["username"];
				$player->email = $userArray["email"];
				$player->receiveNotifications = $userArray["receiveNotifications"];
				
				return $player;
				
				
			}else{
				// Password failed
				return false;
			}
		}
		
		/**
		 * checkPassword($hashedPasswordPlusHash, $enteredPassword)
		 * 
		 * Salts the entered password, hashes, 
		 * and compares to stored password.
		 */
		private static function checkPassword($hashedPasswordPlusHash, $enteredPassword){
			$salt = substr($hashedPasswordPlusHash, self::SALT_LENGTH * -1);
			
			$hashedPassword = sha1($enteredPassword . $salt);
			
			return ($hashedPassword . $salt) == $hashedPasswordPlusHash;
		}

	}

?>