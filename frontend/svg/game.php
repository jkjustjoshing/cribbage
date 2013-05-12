<!DOCTYPE html>
<html>
	<head>
		<title>Cribbage</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<script src="../js/jquery.min.js"></script>

		<script type="text/javascript" src="../js/chat.js"></script>
		<script type="text/javascript" src="../js/ajax.js"></script>
		<script type="text/javascript" src="../js/jquery.svg.min.js"></script>
		<script type="text/javascript" src="../js/jquery.svganim.js"></script>

		<script type="text/javascript" src="../js/scoreboard-ck.js"></script>
		<link type="text/css" rel="stylesheet" href="../css/chat.css" />
		<link type="text/css" rel="stylesheet" href="../css/game.css" />
		<script type="text/javascript">
			$(document).ready(function(){

				

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
			<?php include("board.svg");?>
		</div>
	</body>
</html>
