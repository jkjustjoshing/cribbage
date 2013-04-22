<?php
	require_once("config.php");

	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");
		
	if(SecurityToken::isTokenSet()){
		$userID = SecurityToken::extract();
		if($userID === false){
			// Token was set, but something was wrong. Logged out.
			// Show login screen with error message
			header("Location: login.php");
		}
	}
	
?><!DOCTYPE html>
<html>
	<head>
		<title>Lobby</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		
		<script type="text/javascript">
			window.opponentID = <?php echo $_GET["o"]; ?>;
			window.playerID = <?php echo $_GET["p"]; ?>;
			
		</script>
		
		<!-- My scripts/styles -->
		<script type="text/javascript" src="js/chat.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<link type="text/css" rel="stylesheet" href="css/chat.css" />
		
	</head>
	<body>
		<div class="container">
			<div class="chat">
				<div class="conversation">
				
				</div>
				<form class="send" action="" method="post" onsubmit="return false">
					<input type="text" name="text" />
				</form>
			</div>
		</div>
	</body>
</html>
