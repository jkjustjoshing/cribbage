<?php
	require_once('config.php');
	
	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/Player.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/gameplay/Gamespace.class.php");

		
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

	// If the gameID isn't an integer, redirect to the lobby
	if(!is_numeric($gameID) || "".intval($gameID) !== $gameID){
		header("Location: lobby.php");
	}
	
	// Get game information
	try{
		$gamespace = new Gamespace($gameID, $userID);
	}catch(Exception $e){
		header("Location: lobby.php");
	}

	$player = new Player($userID);
	$opponent = new Player($gamespace->getOpponentID());
	
?><!DOCTYPE html>
<html>
	<head>
		<title>Cribbage</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<script src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/chat.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<link type="text/css" rel="stylesheet" href="css/chat.css" />
		<link type="text/css" rel="stylesheet" href="css/game.css" />
		<script type="text/javascript" src="js/jquery.svg.min.js"></script>
		<script type="text/javascript" src="js/jquery.svganim.js"></script>
		<script type="text/javascript" src="js/gameplay/Scoreboard.class-ck.js"></script>
		<script type="text/javascript" src="js/gameplay/PlayingCard.class.js"></script>
		<script type="text/javascript" src="js/gameplay/PlayerHand.class.js"></script>
		<script type="text/javascript" src="js/gameplay/CardDeck.class.js"></script>
		<script type="text/javascript" src="js/gameplay/Gamespace.class.js"></script>
		<script type="text/javascript" src="js/gameplay/Crib.class.js"></script>
		<script type="text/javascript" src="js/init.js"></script>
		<script type="text/javascript">

<?php
	
	$textColor = "purple";
	
?>


			window.textColor = "<?php echo $textColor; ?>";

			window.gameID = <?php echo $gameID; ?>;
				
			window.player = 
				{"id" : <?php echo $userID; ?> , "username" : '<?php echo $player->username; ?>'};
			window.opponent = 
				{"id" : <?php echo $opponent->id;?>, "username" : '<?php echo $opponent->username; ?>'};
		
			$(document).ready(function(){
				window.chats = {};
				window.chats[window.opponent.id] = new Chat($("#gameChat"), window.opponent.id);

				window.scoreboard = new Scoreboard(
					{
						score: <?php $scores = $gamespace->getScores(); echo $scores[$player->id]; ?>, 
						id: <?php echo $player->id; ?>, 
						username: '<?php echo $player->username; ?>', 
						backPinPosition: <?php $backPins = $gamespace->getBackPinPositions(); echo $backPins[$player->id]; ?>
					},
					{
						score: <?php echo $scores[$opponent->id]; ?>, 
						id: <?php echo $opponent->id; ?>, 
						username: '<?php echo $opponent->username; ?>', 
						backPinPosition: <?php echo $backPins[$opponent->id]; ?>
					}
				);
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
				<rect x="0" y="0" width="100%" height="100%" id="background" />
				
				<text id="statusMessage" x="100" y="260" font-family="Arial" font-size="20" fill="<?php echo $textColor; ?>"></text>
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
