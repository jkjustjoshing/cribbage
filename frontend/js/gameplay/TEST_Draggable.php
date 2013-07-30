	<!DOCTYPE html>
<html>
	<head>
		<title>Cribbage</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<script type="text/javascript" src="../jquery.min.js"></script>
		<script type="text/javascript" src="../jquery.svg.min.js"></script>
		<script type="text/javascript" src="../jquery.svganim.js"></script>
		<script type="text/javascript" src="Draggable.class.js"></script>

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

			text {
				-webkit-touch-callout: none;
				-webkit-user-select: none;
				-khtml-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none;
				user-select: none;
				cursor: default;
			}
			
		</style>
	</head>
	<body>
		<div id="gameContainer">
						<svg xmlns="http://www.w3.org/2000/svg" 
				version="1.1"  width="900px" height="700px" id="table">
				<!-- Make the background -> 800x600 -->

				<rect x="0" y="0" width="100%" height="100%" id="background" fill="grey" />
				<rect x="20" y="20" width="200" height="200" fill="blue" />
				<rect x="400" y="400" width="200" height="200" fill="red" />
				<text id="statusMessage0" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage1" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage2" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage3" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage4" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage5" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage6" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage7" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage8" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage9" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage10" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage11" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
				<text id="statusMessage12" x="100" y="240" font-family="Arial" font-size="20" fill="brown">foofers</text>
			</svg>
		</div>
		<div class="chat" id="gameChat">
			<div class="conversation">
		
			</div>
			<form class="send" action="" method="post">
				<input type="text" name="text" />
			</form>
		</div>
		<script type="text/javascript">
			for(var i = 0; i < 13; ++i){
				var draggable = new Draggable(document.getElementById("statusMessage"+i));
				//draggable.addTarget(targetCoordinates, successCoordinates, callback(true/false success))

				draggable.addTarget({
					coordinates: {
							x: 20,
							y: 20,
							width: 200,
							height:200
					}, 
					success: function(element, target){
						this.removeTarget(target);
					}
				});

				draggable.addTarget({
					coordinates:{
						x: 400,
						y: 400,
						width: 200,
						height:200
					}, 
					success: function(element, target){
						this.removeTarget(target);
					}
				});
			}
		</script>

	</body>
</html>
