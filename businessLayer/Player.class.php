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
		
		const USERNAME_WHITELIST = "/^[a-zA-Z0-9_]+$/";
		const EMAIL_REGEX = '/^[a-zA-Z0-9_+.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,8}$/';
		const MIN_PASSWORD_CHARS = 8;
		
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
			
			$userArray = $database->getPlayer($username);
			
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
		 * createAccount($username, $password, $email)
		 * 
		 * Creates an account with the passed information
		 * @param $username The user's desired username
		 * @param $password The password of the user
		 * @param $email The user's email
		 * @return string error message on error, empty string on success
		 */
		public static function createAccount($username, $password, $email){
			if(!preg_match(self::USERNAME_WHITELIST, $username)){
				// Username doesn't match whitelist.
				return "Username had invalid characters.";
			}
			
			if(!preg_match(self::EMAIL_REGEX, $email)){
				// Email doesn't match email format.
				return "Email is not valid.";
			}
			
			if(strlen($password) < self::MIN_PASSWORD_CHARS){
				// Password isn't long enough
				return "Password is not long enough.";
			}
			
			// Inputs are good - check database and add
			$database = DataLayer::getInstance();
			
			if($database->getPlayer($username) !== false){
				// Got data back - username already exists
				return "That username already exists. Please choose another one.";
			}
			
			// Username doesn't already exist - hash password and add all to database!!
			$hashedPassword = self::saltAndHash($password);
			$success = $database->createPlayer($username, $hashedPassword, $email);
		
			if($success){
				return "";
			}else{
				return "There was an error. Please try again or contact the administrator.";
			}
				
		}

	}

?>