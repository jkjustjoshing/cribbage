<?php
	require_once('config.php');
	
	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");
	
	SecurityToken::unsetToken();
	header("Location: login.php");
	
?>