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
		<script src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/chat.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<link type="text/css" rel="stylesheet" href="css/chat.css" />
		<link type="text/css" rel="stylesheet" href="css/game.css" />
		<script type="text/javascript" src="js/jquery.svg.min.js"></script>
		<script type="text/javascript" src="js/jquery.svganim.js"></script>
		<script type="text/javascript" src="js/scoreboard-ck.js"></script>
		<script type="text/javascript" src="js/gameplay/PlayingCard.class.js"></script>
		<script type="text/javascript">
			/**
			 * SVG Namespace global variable
			 * @type {String}
			 */
			var svgns = "http://www.w3.org/2000/svg";
				
			var xlinkns = "http://www.w3.org/1999/xlink";
			var xhtmlns = "http://www.w3.org/1999/xhtml";

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
			<?php include(dirname(__FILE__) . "/svg/board.svg");?>
			
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
				<rect width="100" height="140" 
		            rx="8" ry="8" 
		            stroke="black" stroke-width="1" 
		            fill="none" />
				<text id="text" x="5" y="20" font-family="Arial" font-size="20" fill="red">8</text>
				<use xlink:href="#text" transform="rotate(180,50,70)"/>
				<path id="club" d="
	                    M 20,70 
	                    Q 28,60 27,50 
	                    C -8,70 -8,17 20,27
	
	                    C -8,-8 68,-8  40,27 

	                    C 68,17 68,70 33,50
	                    Q 32,60 40,70" transform="translate(42.5,61.25) scale(0.25) " fill="black" />


			</svg>
		</div>
		<div class="chat" id="gameChat">
			<div class="conversation">
		
			</div>
			<form class="send" action="" method="post">
				<input type="text" name="text" />
			</form>
		</div>
	</body>
</html>
