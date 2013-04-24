<?php

	class SecurityToken{
		
		private static $ip_base = 4;
		private static $timestamp_base = 2;
		private static $userID_base = 11;
		private static $filler = "f"; //the bases are all less than 16, so no f will be used
		private static $normalized_length = 40; // Length of SHA1 digest
		private static $cookieName = "random_token_identifier";
		private static $cookieExpire;
		const RETURN_VAL = true;

		public static function create($userID, $return = false){
			self::$cookieExpire = time() + (60*60);
		
			$userAgent = $_SERVER["HTTP_USER_AGENT"];
			$ip = $_SERVER["REMOTE_ADDR"];
			$timestamp = time();
			
			$userAgent_sha = sha1($userAgent);
			
			//IP Address
			$ip_converted_base = base_convert($ip, 10, self::$ip_base);
			$ip_converted_base = str_pad($ip_converted_base, self::$normalized_length, self::$filler);
			$ip_sha = sha1($ip_converted_base);
			
			//Timestamp
			$timestamp_converted_base = base_convert($timestamp, 10, self::$timestamp_base);
			$timestamp_converted_base = str_pad($timestamp_converted_base, self::$normalized_length, self::$filler);
			$timestamp_sha = sha1($timestamp_converted_base);
			
			$userID_converted_base = base_convert($userID, 10, self::$userID_base);
			$userID_converted_base = str_pad($userID_converted_base, self::$normalized_length, self::$filler);
			$userID_sha = sha1($userID_converted_base);

			$cumulative_sha = sha1($userAgent_sha . $ip_sha . $timestamp_sha . $userID_sha);

			// Interleave ip_converted, timestamp_converted, userid_converted,
			// and cumulative sha
			$timestampArr = str_split($timestamp_converted_base);
			$userIDArr = str_split($userID_converted_base);
			$ipArr = str_split($ip_converted_base);
			$userAgent_shaArr = str_split($userAgent_sha);
			$cumulative_shaArr = str_split($cumulative_sha);
			$combinedArr = array();

			for($i = 0; $i < self::$normalized_length; ++$i){
				$combinedArr[] = $timestampArr[$i] . $userIDArr[$i] . $ipArr[$i] . $userAgent_shaArr[$i] . $cumulative_shaArr[$i];
			}

			$combinedStr = implode($combinedArr);

			if($return){
				return $combinedStr;
			}else{
				setcookie(self::$cookieName, $combinedStr, self::$cookieExpire);
			}
		}

		public static function isTokenSet(){
			if(isset($_COOKIE[self::$cookieName]) && $_COOKIE[self::$cookieName] !== ""){
				return true;
			}

			return false;
		}

		public static function unsetToken(){
			setcookie(self::$cookieName, "");
		}

		public static function extract($token = "", $return = false){
			self::$cookieExpire = time() + (60*60);
			
			if($token === ""){
				$token = $_COOKIE[self::$cookieName];
			}

			$tokenCharArr = str_split($token);
			$timestampArr = array();
			$userIDArr = array();
			$ipArr = array();
			$userAgent_shaArr = array();
			$cumulative_shaArr = array();

			foreach($tokenCharArr as $index=>$tokenChar){
				switch($index%5){
					case 0:
						$timestampArr[] = $tokenChar;
						break;
					case 1:
						$userIDArr[] = $tokenChar;
						break;
					case 2:
						$ipArr[] = $tokenChar;
						break;
					case 3:
						$userAgent_shaArr[] = $tokenChar;
						break;
					case 4:
						$cumulative_shaArr[] = $tokenChar;
						break;
				}
			}
			
			$timestamp_string = implode($timestampArr);
			$userID_string = implode($userIDArr);
			$ip_string = implode($ipArr);
			$userAgent_sha = implode($userAgent_shaArr);
			$cumulative_sha = implode($cumulative_shaArr);
			

			// Test user agent SHA
			$userAgent_sha_compare = sha1($_SERVER["HTTP_USER_AGENT"]);
			$cumulative_sha_compare = sha1($userAgent_sha . sha1($ip_string) . sha1($timestamp_string) . sha1($userID_string));


			$timestamp_base_converted = trim($timestamp_string, self::$filler);
			$userID_base_converted = trim($userID_string, self::$filler);
			$ip_base_converted = trim($ip_string, self::$filler);
			
			$timestamp = base_convert($timestamp_base_converted, self::$timestamp_base, 10);
			$userID = base_convert($userID_base_converted, self::$userID_base, 10);
			$ip = base_convert($ip_base_converted, self::$ip_base, 10);
			
			if($return){
				return array("IP" => $ip, "USER" => $userID, "TIMESTAMP" => $timestamp);
			}else{
				if($ip !== base_convert($ip, 10, 10)){
					self::unsetToken();
					return false;
				}

				if($timestamp > self::$cookieExpire){
					self::unsetToken();
					return false;
				}
				
				if($userAgent_sha !== $userAgent_sha_compare){
					self::unsetToken();
					return false;
				}

				return $userID;

			}
		}
		
		public static function checkId($idToCheck){
			if(!self::isTokenSet()){
				return false;
			}
			
			$tokenID = self::extract();
			
			if($tokenID === false || $idToCheck !== $tokenID){
				return false;
			}
			
			return true;
		}

	}

?>
