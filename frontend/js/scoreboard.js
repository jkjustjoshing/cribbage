/**
 * Scoreboard object
 * @author  Josh Kramer
 * 
 * Encapsulates the scoreboard manipulation, positioning, and storing of the score.
 * All scores are computed and stored server side. Therefore, if a user manipulates
 * the score stored in this object it will not affect the state of the game server 
 * side.
 * 
 * @param {object} player1Info The "score", "id", and "backPinPosition" for player 1
 * @param {object} player2Info The "score", "id", and "backPinPosition" for player 2
 */
function Scoreboard(player1Info, player2Info){
	var i, circle;
	this.playerInfo = [];
	this.playerInfo[player1Info.id] = player1Info;
	this.playerInfo[player2Info.id] = player2Info;

	// Essentially figures out who is red and who is blue
	if(this.playerInfo[player1Info.id].index === undefined){
		this.playerInfo[player1Info.id].index = 0;
		this.playerInfo[player2Info.id].index = 1;
	}

	// Create the pegs for each user
	this.playerInfo[player1Info.id].pins = [];
	for(i = 0; i < 2; ++i){
		circle = document.createElementNS(svgns, "circle");
		circle.setAttributeNS(null, "r", "4");
		circle.setAttributeNS(null, "cx", "0");
		circle.setAttributeNS(null, "cy", "0");
		circle.setAttributeNS(null, "fill", "black");

		this.playerInfo[player1Info.id].pins[i] = circle;
		document.getElementById	("scoreboard").appendChild(circle);
	}
	var realScore = this.playerInfo[player1Info.id].score;
	this.changeScore(player1Info.id, this.playerInfo[player1Info.id].backPinPosition);
	this.changeScore(player1Info.id, realScore);

	this.playerInfo[player2Info.id].pins = [];
	for(i = 0; i < 2; ++i){
		circle = document.createElementNS(svgns, "circle");
		circle.setAttributeNS(null, "r", "4");
		circle.setAttributeNS(null, "cx", "0");
		circle.setAttributeNS(null, "cy", "0");
		circle.setAttributeNS(null, "fill", "black");

		this.playerInfo[player2Info.id].pins[i] = circle;
		document.getElementById("scoreboard").appendChild(circle);
	}
	realScore = this.playerInfo[player2Info.id].score;
	this.changeScore(player2Info.id, this.playerInfo[player2Info.id].backPinPosition);
	this.changeScore(player2Info.id, realScore);

	console.log("Error - Scoreboard object doesn't work once pegging hits 121 due to the SVG object not having a winning peg. scoreboard.js, line ~57.");

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
		//Update the score in memory, update the pin locations on the screen
		this.playerInfo[playerID].backPinPosition = this.playerInfo[playerID].score;
		this.playerInfo[playerID].score = newScore;

		var tempPin = this.playerInfo[playerID].pins[0];
		this.playerInfo[playerID].pins[0] = this.playerInfo[playerID].pins[1];
		this.playerInfo[playerID].pins[1] = tempPin;
		this.movePiece(this.playerInfo[playerID].pins[0], this.coordinates[this.playerInfo[playerID].index][newScore]);
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
