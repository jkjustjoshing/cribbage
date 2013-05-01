<?php
	require_once('config.php');
	
	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/Player.class.php");
		
	// Check login
	if(SecurityToken::isTokenSet()){
		$userID = SecurityToken::extract();

		if($userID === false){
			// Token was set, but something was wrong. Logged out.
			// Show login screen
			header("Location: login.php");
		}
	}else{
		// No token, redirect to page
		header("Location: login.php");
	}
	
	// Check gameID exists
	if(!isset($_GET["gameID"])){
		header("Location: lobby.php");
	}
	$gameID = $_GET["gameID"];
	
	// Get game information
	
	
	
?><!DOCTYPE html>
<html>
	<head>
		<title>Cribbage</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<script type="text/javascript" src="js/chat.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<link type="text/css" rel="stylesheet" href="css/chat.css" />
		<link type="text/css" rel="stylesheet" href="css/game.css" />
		<script src="js/jquery.min.js"></script>
		<script type="text/javascript">
			window.gameID = <?php echo $gameID; ?>;
		
			window.playerInfo = {0:"lobby"};
		
			window.player = 
				{"id" : <?php echo $userID; ?> };
			window.opponent = 
				{"id" : 0};
		
			$(document).ready(function(){
				window.chats = {};
				window.chats[window.opponent.id] = new Chat($("#gameChat"), window.opponent.id);
			});
		</script>
		<style type="text/css">
			#board {
				position:absolute;
				top: 0px;
			}
			#table {
				position:absolute;
				left:150px;
				padding-right:200px;
			}
		</style>
	</head>
	<body>
		<div id="gameContainer">
			<embed id="board" src="svg/board.svg" width="100" height="522"/>
			
			<svg xmlns="http://www.w3.org/2000/svg" 
				version="1.1"  width="900px" height="700px" id="table">
				<!-- Make the background -> 800x600 -->
				<rect x="0px" y="0px" width="100%" height="100%" id="background" />
				<text x="20px" y="20px" id="youPlayer">
					You are:
				</text>
				<text x="270px" y="20px" id="nyt" fill="red" display="none">
					NOT YOUR TURN!
				</text>
				<text x="270px" y="20px" id="nyp" fill="red" display="none">
					NOT YOUR PIECE!
				</text>
				<text x="520px" y="20px" id="opponentPlayer">
					Opponent is:
				</text>
				<text x="650px" y="150px" id="output">
					cell id
				</text>
				<text x="650px" y="190px" id="output2">
					piece id
				</text>
			</svg>
		</div>
		<div class="chat" id="gameChat">
			<div class="challenge"></div>
			<div class="conversation">
		
			</div>
			<form class="send" action="" method="post">
				<input type="text" name="text" />
			</form>
		</div>
	</body>
</html>
