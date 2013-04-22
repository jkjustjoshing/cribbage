<?php

	// Require the SiteConfiguration object
	require_once("../../../SiteConfig.class.php");

	// Require token checking
	require_once("../../../SecurityToken.class.php");

	if(isset($_GET["method"] && isset($_GET["application"])){
		$method = $_GET["method"];
		$application = $_GET["application"];
		$data = $_GET["data"];
	}else if(isset($_POST["method"]) && isset($_POST["application"])){
		$method = $_POST["method"];
		$application = $_POST["application"];
		$data = $_POST["data"];
	}
	

	if(is_array(SiteConfig::POSSIBLE_METHODS[$application]) && in_array($method, SiteConfig::POSSIBLE_METHODS[$application])){
		
		// We have a valid application and method. Require the files

		foreach( glob(SiteConfiguration::BASE_DIR . "/businessLogic/" . $application . "/") as $filename){
			require_once($filename);
		}
		
		$serviceMethod=$_REQUEST['method'];
		$data=$_REQUEST['data'];
		$result=@call_user_func($serviceMethod,$data,$_SERVER['REMOTE_ADDR'],$_COOKIE['token']);
		if($result){
			//might need the header cache stuff
			header("Content-Type:text/plain");
			echo $result;
		}
	}
?>		
				
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
