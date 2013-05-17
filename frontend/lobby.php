<?php
	require_once("config.php");

	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/Chat.class.php");
		
	if(SecurityToken::isTokenSet()){
		$userID = SecurityToken::extract();
		if($userID === false){
			// Token was set, but something was wrong. Logged out.
			// Show login screen with error message
			header("Location: login.php");
		}
	}else{
		header("Location: login.php");
	}
	
?><!DOCTYPE html>
<html>
	<head>
		<title>Lobby</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<script type="text/javascript" src="js/jquery.min.js"></script>
		
		<script type="text/javascript">
			window.player = {"id":<?php echo $userID; ?>};
		</script>
		
		<!-- My scripts/styles -->
		<script type="text/javascript" src="js/chat.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<script type="text/javascript" src="js/challenge.js"></script>
		<link type="text/css" rel="stylesheet" href="css/chat.css" />
		<script type="text/javascript">
			$(document).ready(function(){
				window.chats = [];
				window.chats[0] = new Chat($("#lobbyChat"), 0);
				window.chats.size = function(){
					var length = 0;
					for(var i = 0; i < window.chats.length; ++i){
						if(window.chats[i] !== undefined){
							++length;
						}
					}
					return length;
				}
			});
		</script>
		
	</head>
	<body>
		<div id="lobbyContainer">
			<div id="onlinePlayers">
				<h2>Online Players</h2>
				<ul></ul>
			</div>
		</div>

		<div id="logout" style="position:absolute;left:300px;">
			<a href="logout.php">Logout</a>
		</div>

		<div class="chat" id="lobbyChat">
			<div class="conversation">
			
			</div>
			<form class="send" action="" method="post">
				<input type="text" name="text" autocomplete="off" />
			</form>
		</div>
	</body>
</html>
