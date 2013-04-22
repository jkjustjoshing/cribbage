<?php

	require_once("config.php");

	// Require the SiteConfiguration object
	require_once(BACKEND_DIRECTORY . "/SiteConfig.class.php");

	// Require token checking
	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");

	if(isset($_GET["method"]) && isset($_GET["application"])){
		$method = $_GET["method"];
		$application = $_GET["application"];
		$data = $_GET["data"];
	}else if(isset($_POST["method"]) && isset($_POST["application"])){
		$method = $_POST["method"];
		$application = $_POST["application"];
		$data = $_POST["data"];
	}
	

	if(is_array(SiteConfig::$POSSIBLE_METHODS[$application]) && in_array($method, SiteConfig::$POSSIBLE_METHODS[$application])){
		
		// We have a valid application and method. Require the files
		require_once(BACKEND_DIRECTORY . "/controller/" . $application . ".php");

				
		// Must take single parameter (possibly array), and return json
		$result = @call_user_func($method, $data);
		
		if($result){
			//might need the header cache stuff
			header("Content-Type:text/plain");
			echo $result;
		}
	}
?>
