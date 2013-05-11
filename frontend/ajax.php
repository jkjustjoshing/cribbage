<?php

	require_once("config.php");

	// Require the SiteConfiguration object
	require_once(BACKEND_DIRECTORY . "/SiteConfig.class.php");

	// Require token checking
	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");

	// Require the code for the heartbeat setting
	require_once(BACKEND_DIRECTORY . "/controller/heartbeat.php");

	if(isset($_GET["method"]) && isset($_GET["application"])){
		$method = $_GET["method"];
		$application = $_GET["application"];
		$data = $_GET["data"];
		$heartbeatRoom = $_GET["heartbeatRoom"];
	}else if(isset($_POST["method"]) && isset($_POST["application"])){
		$method = $_POST["method"];
		$application = $_POST["application"];
		$data = $_POST["data"];
		$heartbeatRoom = $_POST["heartbeatRoom"];
	}
	
	$result = array();

	// Actions to take no matter what the method/application is
	$result["info"] = array("time" => time());

	if(isset($heartbeatRoom) && intval($heartbeatRoom) >= 0){
		//Write the heartbeat
		heartbeat($heartbeatRoom);
	}


	// End actions to take no matter what the method/application is

	// Application/method dependent code
	if(is_array(SiteConfig::$POSSIBLE_METHODS[$application]) && in_array($method, SiteConfig::$POSSIBLE_METHODS[$application])){
		
		// We have a valid application and method. Require the files
		require_once(BACKEND_DIRECTORY . "/controller/" . $application . ".php");

		// Function must take single parameter (possibly array), and return an array!
		$resultArr = @call_user_func($method, $data);
		
		if(is_array($resultArr)){
			// The result is an array of the data
			$result[$application] = $resultArr;
		}else{
			// The result is actually a string containing an error message
			$result[$application] = array("error" => $resultArr);
		}
		
	}else{
		$result[$application] = array("error" => "The " . $application . " method '" . $method . "' doesn't exist.");
	}
	// End application/method dependent code

	
	
		
	//might need the header cache stuff
	header("Content-Type:text/plain");
		
	echo json_encode($result, JSON_HEX_TAG);
	
?>
