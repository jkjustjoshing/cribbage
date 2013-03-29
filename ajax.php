<?php

	// Require the SiteConfiguration object
	require_once("../../../SiteConfig.class.php");

	// Require token checking
	require_once("../../../SecurityToken.class.php");

	if(isset($_GET["method"] && isset($_GET["application"])){
		$method = $_GET["method"];
		$application = $_GET["application"];		
	}else if(isset($_POST["method"]) && isset($_POST["application"])){
		$method = $_POST["method"];
		$application = $_POST["application"];
	}
	

	if(is_array(SiteConfig::POSSIBLE_METHODS[$application]) && in_array($method, SiteConfig::POSSIBLE_METHODS[$application])){
		
		// We have a valid application and method. Require the files

		require(SiteConfiguration::BASE_DIR . 

	}


/*
	if(!SecurityToken::isTokenSet()){
		// Handle not logged in
	}else{
		// Token set - see if token is valid
		$tokenResult = SecurityToken::extract();
		if($tokenResult === false){
			// Handle not logged in
		}else{
			$userID = $tokenResult; // Successfull token, this is the userID
		}
	}
*/		

?>
