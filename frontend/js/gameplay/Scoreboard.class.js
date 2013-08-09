/**
 * SVG Namespace global variable
 * @type {String}
 */
var svgns = "http://www.w3.org/2000/svg";

//var xlinkns = "http://www.w3.org/1999/xlink";
//var xhtmlns = "http://www.w3.org/1999/xhtml";

/**
 * Scoreboard object
 * @author  Josh Kramer
 * 
 * Encapsulates the scoreboard manipulation, positioning, and storing of the score.
 * All scores are computed and stored server side. Therefore, if a user manipulates
 * the score stored in this object it will not affect the state of the game server 
 * side.
 * 
 * @param {object} player1Info The "score", "id", "username", and "backPinPosition" for player 1
 * @param {object} player2Info The "score", "id", "username", and "backPinPosition" for player 2
 */
function Scoreboard(player1Info, player2Info, scoreboardEle, gameEle){
	var i, circle;
	this.playerInfo = {};
	this.playerInfo[player1Info.id] = player1Info;
	this.playerInfo[player2Info.id] = player2Info;

	// Essentially figures out who is red and who is blue
	if(this.playerInfo[player1Info.id].index === undefined){
		this.playerInfo[(player1Info.id > player2Info.id ? player1Info.id : player2Info.id)].index = 0;
		this.playerInfo[(player1Info.id > player2Info.id ? player2Info.id : player1Info.id)].index = 1;
	}

	// Create the pegs for player 1
	this.playerInfo[player1Info.id].pins = [];
	for(i = 0; i < 2; ++i){
		circle = document.createElementNS(svgns, "circle");
		circle.setAttributeNS(null, "r", "4");
		circle.setAttributeNS(null, "cx", "0");
		circle.setAttributeNS(null, "cy", "0");
		circle.setAttributeNS(null, "fill", "black");

		this.playerInfo[player1Info.id].pins[i] = circle;
		scoreboardEle.appendChild(circle);
	}

	// Create the pegs for player 2
	this.playerInfo[player2Info.id].pins = [];
	for(i = 0; i < 2; ++i){
		circle = document.createElementNS(svgns, "circle");
		circle.setAttributeNS(null, "r", "4");
		circle.setAttributeNS(null, "cx", "0");
		circle.setAttributeNS(null, "cy", "0");
		circle.setAttributeNS(null, "fill", "black");

		this.playerInfo[player2Info.id].pins[i] = circle;
		scoreboardEle.appendChild(circle);
	}


	// Create text for user's scores
	var xCoordinate = 45;
	var player1Score = document.createElementNS(svgns, "text");
	player1Score.setAttributeNS(null, "font-family", "Arial");
	player1Score.setAttributeNS(null, "font-size", "20");
	player1Score.setAttributeNS(null, "fill", "red");
	player1Score.setAttributeNS(null, "x", xCoordinate);
	var player1ScoreWord = document.createElementNS(svgns, "text");
	player1ScoreWord.setAttributeNS(null, "font-family", "Arial");
	player1ScoreWord.setAttributeNS(null, "font-size", "20");
	player1ScoreWord.setAttributeNS(null, "text-decoration", "underline");
	player1ScoreWord.setAttributeNS(null, "fill", "red");
	player1ScoreWord.setAttributeNS(null, "x", xCoordinate-17);
	player1ScoreWord.appendChild(document.createTextNode("Score"));
	
	var player2Score = document.createElementNS(svgns, "text");
	player2Score.setAttributeNS(null, "font-family", "Arial");
	player2Score.setAttributeNS(null, "font-size", "20");
	player2Score.setAttributeNS(null, "fill", "#22f");
	player2Score.setAttributeNS(null, "x", xCoordinate);
	var player2ScoreWord = document.createElementNS(svgns, "text");
	player2ScoreWord.setAttributeNS(null, "font-family", "Arial");
	player2ScoreWord.setAttributeNS(null, "font-size", "20");
	player2ScoreWord.setAttributeNS(null, "text-decoration", "underline");
	player2ScoreWord.setAttributeNS(null, "fill", "#22f");
	player2ScoreWord.setAttributeNS(null, "x", xCoordinate-17);
	player2ScoreWord.appendChild(document.createTextNode("Score"));


	this.playerInfo[window.player.id].scoreText = (this.playerInfo[window.player.id].index === 0 ? player1Score : player2Score);
	this.playerInfo[window.opponent.id].scoreText = (this.playerInfo[window.opponent.id].index === 0 ? player1Score : player2Score);
	this.playerInfo[window.player.id].scoreTextWord = (this.playerInfo[window.player.id].index === 0 ? player1ScoreWord : player2ScoreWord);
	this.playerInfo[window.opponent.id].scoreTextWord = (this.playerInfo[window.opponent.id].index === 0 ? player1ScoreWord : player2ScoreWord);

	// Set the score text
	this.playerInfo[window.player.id].scoreText.appendChild(document.createTextNode(this.playerInfo[window.player.id].score));
	this.playerInfo[window.opponent.id].scoreText.appendChild(document.createTextNode(this.playerInfo[window.opponent.id].score));


	var heightDifference = 25;
	var playerY = 630;
	var opponentY = 150;
	this.playerInfo[window.player.id].scoreText.setAttributeNS(null, "y", playerY);
	this.playerInfo[window.player.id].scoreTextWord.setAttributeNS(null, "y", playerY - heightDifference);
	this.playerInfo[window.opponent.id].scoreText.setAttributeNS(null, "y", opponentY);
	this.playerInfo[window.opponent.id].scoreTextWord.setAttributeNS(null, "y", opponentY - heightDifference);


	gameEle.appendChild(this.playerInfo[window.player.id].scoreText);
	gameEle.appendChild(this.playerInfo[window.opponent.id].scoreText);
	gameEle.appendChild(this.playerInfo[window.player.id].scoreTextWord);
	gameEle.appendChild(this.playerInfo[window.opponent.id].scoreTextWord);


	// Update the initial scores
	var realScore = this.playerInfo[player1Info.id].score;
	this.changeScore(player1Info.id, this.playerInfo[player1Info.id].backPinPosition);
	this.changeScore(player1Info.id, realScore);

	realScore = this.playerInfo[player2Info.id].score;
	this.changeScore(player2Info.id, this.playerInfo[player2Info.id].backPinPosition);
	this.changeScore(player2Info.id, realScore);

	

	window.console.log("Error - Scoreboard object doesn't work once pegging hits 121 due to the SVG object not having a winning peg. scoreboard.js, line ~57.");

}

/**
 * Methods for the Scoreboard object
 */
Scoreboard.prototype = {

	/**
	 * Scoreboard::changeScore
	 * Changes the score for a given user, both in memory and on the scoreboard.
	 * 
	 * @param {int} playerID The global playerID of the player for whom to change the score
	 * @param {int} newScore The new score of the player, to update on the screen
	 */
	changeScore: function(playerID, newScore){
		if(newScore === this.playerInfo[playerID].score && newScore !== 0){
			return;
		}

		if(!(window.gamespace && window.gamespace.gamestate === "IN_PROGRESS")){
			return;
		}

		//Update the score in memory
		this.playerInfo[playerID].backPinPosition = this.playerInfo[playerID].score;
		this.playerInfo[playerID].score = newScore;

		// Update the pin positions on screen
		var tempPin = this.playerInfo[playerID].pins[0];
		this.playerInfo[playerID].pins[0] = this.playerInfo[playerID].pins[1];
		this.playerInfo[playerID].pins[1] = tempPin;
		this.movePiece(this.playerInfo[playerID].pins[0], this.coordinates[this.playerInfo[playerID].index][newScore]);
		
		// Update the text score
		this.playerInfo[playerID].scoreText.firstChild.nodeValue = newScore;
	},

	/**
	 * Scoreboard::addPoints
	 * Adds points to the score of o given user, both in memory and on the scoreboard
	 * (by calling changeScore())
	 * 
	 * @param  {int} playerID The global playerID of the player for whom to add the points to
	 * @param  {int} points The number of points to add to the current score of the user
	 */
	addPoints: function(playerID, points){
		// Check for over 121, then call changeScore
		var newScore = this.playerInfo[playerID].score + points;
		if(newScore > 121){
			newScore = 121;
		}

		this.changeScore(playerID, newScore);

		if(newScore === 121){
			window.gamespace.declareWinner(playerID);
		}
	},

	/**
	 * Scoreboard::movePiece
	 * Method to only be used privately, physically moves the pins in the DOM
	 * 
	 * @param  {DOM Element} piece The &lt;circle&gt; element representing the piece to move 
	 * @param  {Array[2]} oldCoordinates The coordinates the piece formerly occupied
	 * @param  {Array[2]} newCoordinates The coordinates the piece should move to
	 */
	movePiece: function(piece, newCoordinates){
		//Physically move the piece
		//piece.setAttributeNS(null, "transform", "translate("+newCoordinates[0]+","+newCoordinates[1]+")");
		$(piece).animate({
			svgTransform: "translate("+newCoordinates[0]+", "+newCoordinates[1]+")"
		}, 400);
	}

};

	//<circle r="4" cx="0" cy="0" fill="black" id="dot" transform="translate(59,499)" />

Scoreboard.prototype.coordinates = [
	// Player 1 (red) coordinates, points 1-120 (121 is winning hole)
	[
		[10, 495], // To fill up the 0th index
		[10,470], // 1
		[10,460], // 2
		[10,450], // 3
		[10,440], // 4
		[10,430], // 5
		[10,410], // 6
		[10,400], // 7
		[10,390], // 8
		[10,380], // 9
		[10,370], // 10
		[10,350], // 11
		[10,340], // 12
		[10,330], // 13
		[10,320], // 14
		[10,310], // 15
		[10,290], // 16
		[10,280], // 17
		[10,270], // 18
		[10,260], // 19
		[10,250], // 20
		[10,230], // 21
		[10,220], // 22
		[10,210], // 23
		[10,200], // 24
		[10,190], // 25
		[10,170], // 26
		[10,160], // 27
		[10,150], // 28
		[10,140], // 29
		[10,130], // 30
		[10,110], // 31
		[10,100], // 32
		[10,90], // 33
		[10,80], // 34
		[10,70], // 35

		// Big Turn
		[10, 50], // 36
		[12,37], // 37
		[17.5,26.5], // 38
		[26,18], // 39
		[38,12], // 40
		[62,12], // 41
		[74,18], // 42
		[82.5,26.5], // 43
		[88,37], // 44
		[90,50], // 45

		[90,70], // 46
		[90,80], // 47
		[90,90], // 48
		[90,100], // 49
		[90,110], // 50
		[90,130], // 51
		[90,140], // 52
		[90,150], // 53
		[90,160], // 54
		[90,170], // 55
		[90,190], // 56
		[90,200], // 57
		[90,210], // 58
		[90,220], // 59
		[90,230], // 60
		[90,250], // 61
		[90,260], // 62
		[90,270], // 63
		[90,280], // 64
		[90,290], // 65
		[90,310], // 66
		[90,320], // 67
		[90,330], // 68
		[90,340], // 69
		[90,350], // 70
		[90,370], // 71
		[90,380], // 72
		[90,390], // 73
		[90,400], // 74
		[90,410], // 75
		[90,430], // 76
		[90,440], // 77
		[90,450], // 78
		[90,460], // 79
		[90,470], // 80

		[90,490], // 81
		[83,506], // 82
		[67.5,512.5], // 83
		[52,506], // 84
		[45,490], // 85

		[45,470], // 86
		[45,460], // 87
		[45,450], // 88
		[45,440], // 89
		[45,430], // 90
		[45,410], // 91
		[45,400], // 92
		[45,390], // 93
		[45,380], // 94
		[45,370], // 95
		[45,350], // 96
		[45,340], // 97
		[45,330], // 98
		[45,320], // 99
		[45,310], // 100
		[45,290], // 101
		[45,280], // 102
		[45,270], // 103
		[45,260], // 104
		[45,250], // 105
		[45,230], // 106
		[45,220], // 107
		[45,210], // 108
		[45,200], // 109
		[45,190], // 110
		[45,170], // 111
		[45,160], // 112
		[45,150], // 113
		[45,140], // 114
		[45,130], // 115
		[45,110], // 116
		[45,100], // 117
		[45,90], // 118
		[45,80], // 119
		[45,70], // 120

		[50,70]  // 120
	],












	// Player 2 (blue) coordinates, points 1-120 (121 is winning hole)
	[
		[20,495], // To fill up the 0th index
		[20,470], // 1
		[20,460], // 2
		[20,450], // 3
		[20,440], // 4
		[20,430], // 5
		[20,410], // 6
		[20,400], // 7
		[20,390], // 8
		[20,380], // 9
		[20,370], // 10
		[20,350], // 11
		[20,340], // 12
		[20,330], // 13
		[20,320], // 14
		[20,310], // 15
		[20,290], // 16
		[20,280], // 17
		[20,270], // 18
		[20,260], // 19
		[20,250], // 20
		[20,230], // 21
		[20,220], // 22
		[20,210], // 23
		[20,200], // 24
		[20,190], // 25
		[20,170], // 26
		[20,160], // 27
		[20,150], // 28
		[20,140], // 29
		[20,130], // 30
		[20,110], // 31
		[20,100], // 32
		[20,90], // 33
		[20,80], // 34
		[20,70], // 35

		// Big Turn
		[20, 50], // 36
		[21.5,41], // 37
		[26,32.5], // 38
		[32,26], // 39
		[41,21.5], // 40
		[59,21.5], // 41
		[68,26], // 42
		[74,32.5], // 43
		[78.5,41], // 44
		[80,50], // 45

		[80,70], // 46
		[80,80], // 47
		[80,90], // 48
		[80,100], // 49
		[80,110], // 50
		[80,130], // 51
		[80,140], // 52
		[80,150], // 53
		[80,160], // 54
		[80,170], // 55
		[80,190], // 56
		[80,200], // 57
		[80,210], // 58
		[80,220], // 59
		[80,230], // 60
		[80,250], // 61
		[80,260], // 62
		[80,270], // 63
		[80,280], // 64
		[80,290], // 65
		[80,310], // 66
		[80,320], // 67
		[80,330], // 68
		[80,340], // 69
		[80,350], // 70
		[80,370], // 71
		[80,380], // 72
		[80,390], // 73
		[80,400], // 74
		[80,410], // 75
		[80,430], // 76
		[80,440], // 77
		[80,450], // 78
		[80,460], // 79
		[80,470], // 80

		[80,490], // 81
		[76,499], // 82
		[67.5,502.5], // 83
		[59,499], // 84
		[55,490], // 85

		[55,470], // 86
		[55,460], // 87
		[55,450], // 88
		[55,440], // 89
		[55,430], // 90
		[55,410], // 91
		[55,400], // 92
		[55,390], // 93
		[55,380], // 94
		[55,370], // 95
		[55,350], // 96
		[55,340], // 97
		[55,330], // 98
		[55,320], // 99
		[55,310], // 100
		[55,290], // 101
		[55,280], // 102
		[55,270], // 103
		[55,260], // 104
		[55,250], // 105
		[55,230], // 106
		[55,220], // 107
		[55,210], // 108
		[55,200], // 109
		[55,190], // 110
		[55,170], // 111
		[55,160], // 112
		[55,150], // 113
		[55,140], // 114
		[55,130], // 115
		[55,110], // 116
		[55,100], // 117
		[55,90], // 118
		[55,80], // 119
		[55,70], // 120

		[50,70]  // 121
	]
];