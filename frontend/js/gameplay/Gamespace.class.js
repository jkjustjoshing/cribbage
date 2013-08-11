function Gamespace(data, svgEle){
	
	// Initialize values
	this.gamestate = data.gamestate;
	this.gamestatus = data.gamestatus;

	this.svgEle = svgEle;

	var playerInfo = {
		score: data.scores[window.player.id],
		id: window.player.id,
		username: window.player.username,
		backPinPosition: data.backPinPositions[window.player.id]
	};
	var opponentInfo = {
		score: data.scores[window.opponent.id],
		id: window.opponent.id,
		username: window.opponent.username,
		backPinPosition: data.backPinPositions[window.opponent.id]
	};
	this.scoreboard = new Scoreboard(playerInfo, opponentInfo, document.getElementById("scoreboard"), this.svgEle, this);

	this.cutCard = data.cutCard;
	this.dealer = data.dealer;

	this.playedCards = new PlayedCards(data.playedCards, document.getElementsByTagName("svg")[1], window.coordinates.playedCards);

	this.hands = [];
	this.hands[window.player.id] = new PlayerHand(data.hands[window.player.id], svgEle, window.coordinates.playerHand);
	this.hands[window.player.id].sort();
	this.hands[window.opponent.id] = new PlayerHand(data.hands[window.opponent.id], svgEle, window.coordinates.opponentHand); 
	this.hands[window.opponent.id].sort();

	this.playerIndex = data.playerIndex;

	var cribCoordinates = [];
	cribCoordinates[window.player.id] = window.coordinates.myCrib;
	cribCoordinates[window.opponent.id] = window.coordinates.opponentCrib;
	this.crib = new Crib(data.hands["crib"], svgEle, cribCoordinates, this.dealer);

	if(this.cutCard.number !== null && this.cutCard.suit !== null){
		this.cutCard = new PlayingCard(this.cutCard["number"], this.cutCard["suit"]);
	}else{
		this.cutCard = null;
	}

	this.deck = new CardDeck(svgEle, this.cutCard);
	this.deck.setDealer(this.dealer, this.gamestate);
	
	this.turn = data.turn;

	// End Initialize values

}

/**
 * Takes the current state and puts the proper listeners on
 * everything and propogates the message to the other objects.
 * The previous state must be cleaned up before this method is called.
 */
Gamespace.prototype.constructState = function(){
	console.log("Constructing game state - " + this.gamestate);

	if(this.gamestatus !== "IN_PROGRESS"){
		return;
	}

	var which = this;
	switch(which.gamestate){
		case "DEALING":
			which.statusMessage("");
			which.deck.setDealer(which.dealer, which.gamestate);
			which.crib.setDealer(which.dealer, which.gamestate);
			// If not dealing, poll for information
			if(which.dealer !== window.player.id){
				var waitingInterval = setInterval(function(){
					ajaxCall("get",
						{
							application:"game",
							method:"getGameState",
							data:{
								gameID: window.gameID
							}
						},
						function(data){
							if(data["game"]["gamestate"] === "CHOOSING_CRIB"){
								clearInterval(waitingInterval);
								which.animateDeal();
							}
						}
					);
				}, 2500);
			}else{
				// If dealing, listeners will be set up by the deck object
			}

			// Set lock for viewing hands - not elegant way to do it but it works
			which.alreadyViewingHands = false;

			break;
		case "CHOOSING_CRIB":
			// Set event listeners on my cards
			which.crib.choosingCribMode(true);
			which.hands[window.player.id].chooseCrib();

			if(which.hands[window.player.id].cards.length == 6){
				which.statusMessage("Choose 2 cards for " + ((window.player.id === this.dealer) ? "my" : (window.opponent.username+"'s")) + " crib.");
			}else{
				which.statusMessage("Waiting for " + window.opponent.username + " to put 2 cards in the crib.");
			}

			// Poll for other player submitting crib cards to update view
			var interval = setInterval(function(){
				ajaxCall(
					"get",
					{
						application: "game",
						method: "getHands",
						data: {
							gameID: window.gameID
						}
					},
					function(data){
						if(data["game"]["crib"].length > which.crib.cards.length){
							// Animate cards to crib from opponent hand
							var card = which.hands[window.opponent.id].remove(new PlayingCard());
							which.crib.add(card);
							which.crib.sort();

							var card = which.hands[window.opponent.id].remove(new PlayingCard());
							which.crib.add(card);

							// Make the second card go to the hand 0.5 seconds after the first
							setTimeout(function(){
								which.crib.sort();
							}, 500);
						}

						if(data["game"]["crib"].length == 4){
							// If the crib has 4 cards stop the polling
							clearInterval(interval);

							// Move to cutting card state
							which.gamestate = "CUTTING_CARD";
							which.constructState();
						}
					}
				);
				// Once crib has 4 cards clear the interval and update the state
			}, 5000);
			break;
		case "CUTTING_CARD":
			// If not dealing spread cards and set listener on each card
			// On click of card, send cut information to server
			which.crib.choosingCribMode(false);

			var cutCardCallback = function(data){
				data = data["game"];
				if(data["error"] !== undefined){
					alert(data["error"]);
				}else{
					if(data["cutCard"]["suit"] !== "" && data["cutCard"]["number"] !== 0){
						// The card has been cut
						if(interval !== undefined){
							clearInterval(interval);
						}

						window.gamespace.deck.updateCutCard(new PlayingCard(data["cutCard"]["number"], data["cutCard"]["suit"]));

						which.gamestate = "PEGGING";
						which.constructState();
					}
				}
			}

			if(which.dealer == window.opponent.id){
				which.statusMessage("Pick a cut card.");
				
				which.deck.pickCutCard(function(cutCardIndex){
					ajaxCall(
						"post",
						{
							application: "game",
							method: "pickCutIndex",
							data: {
								gameID: window.gameID,
								index: cutCardIndex
							}
						},
						cutCardCallback
					);
				});

				
			}else{
				which.statusMessage("Waiting for " + window.opponent.username + " to pick the cut card.");
				var interval = setInterval(function(){
					ajaxCall(
						"get",
						{
							application: "game",
							method: "getCutCard",
							data: {
								gameID: window.gameID
							}
						},
						cutCardCallback
					);
				}, 2000);
			}
			break;
		case "PEGGING":
			which.hands[window.player.id].peggingMode(true);
			which.playedCards.startPolling();
			which.setTurn(this.turn);
			break;
		case "VIEWING_HANDS":
			which.viewHands(true);

			// Confirm each user's set of points, add to board
			// Once all points have been confirmed, send server the ready message
			// Move to appropriate stage based on the returned new state
			break
		case "WAITING_PLAYER_1":
			which.viewHands(which.playerIndex[window.player.id] === 1);
			break;
		case "WAITING_PLAYER_2":
			which.viewHands(which.playerIndex[window.player.id] === 2);
			break;
		default:
			// Just throw them out to the lobby
			alert("Uh oh! Something went wrong! The game doesn't know what state it is in. Try reopening the game.");
			window.close();
	}
}

Gamespace.prototype.statusMessage = function(message){
	var statusMessage = document.getElementsByTagName("svg")[1].getElementById("statusMessage");
	if(statusMessage.lastChild !== null){
		statusMessage.removeChild(statusMessage.lastChild);
	}
	document.getElementById("statusMessage").appendChild(document.createTextNode(message));
}

Gamespace.prototype.animateDeal = function(){
	var which = this;
	ajaxCall(
		"get",
		{
			application:"game",
			method:"getGameData",
			data:{
				gameID: window.gameID
			}
		},
		function(data){
			data = data["game"];
			console.log(which.hands);

			var maxCards = (data.hands[window.player.id].length > data.hands[window.opponent.id].length ? data.hands[window.player.id].length : data.hands[window.opponent.id].length);
			var nonDealer = window.player.id + window.opponent.id - data.dealer;

			var alt_i = 0;
			for(var i = 0; i < maxCards; ++i){
				if(data.hands[nonDealer][i] !== undefined){
					setTimeout(function(){
						which.hands[nonDealer].add(new PlayingCard(data.hands[nonDealer][alt_i].number, data.hands[nonDealer][alt_i].suit)); // TODO create new PlayerHand object
					}, i*300);
				}

				if(data.hands[data.dealer][i] !== undefined){
					setTimeout(function(){
						which.hands[data.dealer].add(new PlayingCard(data.hands[data.dealer][alt_i].number, data.hands[data.dealer][alt_i].suit)); // TODO create new PlayerHand object
						++alt_i;
					}, i*300 + 150);
				}	
			}

			setTimeout(function(){
				which.hands[window.player.id].sort();
				which.gamestate = "CHOOSING_CRIB";
				which.constructState();
			}, i*300 + 500);
		}
	);
}

Gamespace.prototype.setTurn = function(turn){
	var falseColor = "#707070";

	if(this.turnTriangles === undefined){
		this.turnTriangles = {};
		//this.turnTriangles[window.player.id];
		var triangle = document.createElementNS(svgns, "path");
		triangle.setAttributeNS(null, "fill", falseColor);
		triangle.setAttributeNS(null, "d", "M -15,0 15,0 0,-12");
		this.turnTriangles[window.player.id] = triangle;
		this.turnTriangles[window.player.id].setAttributeNS(null, "transform", "translate(70,380) rotate(180,0,0)");

		triangle = document.createElementNS(svgns, "path");
		triangle.setAttributeNS(null, "fill", falseColor);
		triangle.setAttributeNS(null, "d", "M -15,0 15,0 0,-12");
		this.turnTriangles[window.opponent.id] = triangle;
		this.turnTriangles[window.opponent.id].setAttributeNS(null, "transform", "translate(70,330)");
		
		document.getElementsByTagName("svg")[1].appendChild(this.turnTriangles[window.player.id]);
		document.getElementsByTagName("svg")[1].appendChild(this.turnTriangles[window.opponent.id]);
	}

	if(turn !== false){
		this.turn = turn;
		this.turnTriangles[window.player.id].setAttributeNS(null, "fill", (window.player.id === this.turn ? window.textColor : falseColor));
		this.turnTriangles[window.opponent.id].setAttributeNS(null, "fill", (window.opponent.id === this.turn ? window.textColor : falseColor));
	}else{
		var trianglePlayer = this.turnTriangles[window.player.id];
		var triangleOpponent = this.turnTriangles[window.opponent.id];
		trianglePlayer.parentNode.removeChild(trianglePlayer);
		triangleOpponent.parentNode.removeChild(triangleOpponent);
		this.turnTriangles = undefined;
	}
}

Gamespace.prototype.viewHands = function(needToConfirmHand){
	var which = this;
	
	// Set lock
	if(which.alreadyViewingHands){
		return;
	}else{
		which.alreadyViewingHands = true;
	}
	
	// Remove the turn indicator arrows	
	which.setTurn(false); // Remove the turn indicator triangle
	
	// Remove the count text
	which.playedCards.updateCountText(null);

	// Remove "go" button
	var hand = which.hands[window.player.id];
	if(hand.goEle !== undefined){
		hand.goEle.parentNode.removeChild(hand.goEle);
		hand.goEle = undefined;
	}

	ajaxCall(
		"get",
		{
			application: "game",
			method: "getGameData",
			data: {
				gameID: window.gameID
			}
		},
		function(data){
			data = data["game"];

			// Create new player hands 
			var playerHand = which.hands[window.player.id];
			playerHand.clear();
			for(var i = 0; i < data["hands"][window.player.id].length; ++i){
				data["hands"][window.player.id][i].inHand = 1;
				var suit = data["hands"][window.player.id][i].suit;
				var number = data["hands"][window.player.id][i].number;
				playerHand.add(new PlayingCard(number, suit), true);
			}
			playerHand.sort();
			
			var opponentHand = which.hands[window.opponent.id];
			opponentHand.clear();
			for(var i = 0; i < data["hands"][window.opponent.id].length; ++i){
				data["hands"][window.opponent.id][i].inHand = 1;
				var suit = data["hands"][window.opponent.id][i].suit;
				var number = data["hands"][window.opponent.id][i].number;
				opponentHand.add(new PlayingCard(number, suit), true);
			}
			opponentHand.sort();
			
			which.crib.viewingCards(data["hands"]["crib"]);
			which.crib.sort();

			if(needToConfirmHand){
				which.confirmHandView(data);
			}else{
				which.statusMessage("Waiting for " + window.opponent.username + " to finish looking at the hands.");
				var interval = setInterval(function(){
					ajaxCall(
						"get",
						{
							application: "game",
							method: "getGameData",
							data: {
								gameID: window.gameID
							}
						},
						function(data){
							data = data["game"];

							if(data.gamestate !== "VIEWING_HANDS" && data.gamestate.indexOf("WAITING_PLAYER_") === -1){
								// Game state not for viewing
								clearInterval(interval);
								which.gamestate = data.gamestate;
								which.resetGamespace();
								which.constructState();
							}
						}
					);
				}, 2000);
			}
		}
	);		
}

Gamespace.prototype.confirmHandView = function(data){
	var which = this;


	var nonDealer = (window.player.id === which.dealer ? window.opponent : window.player);

	var nonDealerScore = data["handPoints"][nonDealer.id];
	which.statusMessage((nonDealer.id === window.player.id ? "Your" : (nonDealer.username + "'s")) + " hand scores "+nonDealerScore+" points.");
	which.scoreboard.addPoints(nonDealer.id, nonDealerScore);

	var okEle = document.createElementNS(svgns, "g");
	var okBox = document.createElementNS(svgns, "rect");
	okEle.appendChild(okBox);
	
	var coordinates = {
		x: (nonDealer.id === window.player.id ? window.coordinates.playerHand.x : window.coordinates.opponentHand.x) + 50,
		y: (nonDealer.id === window.player.id ? window.coordinates.playerHand.y : window.coordinates.opponentHand.y) + 60
	};

	okEle.setAttributeNS(null, "transform", "translate("+coordinates.x+","+coordinates.y+")");

	okBox.setAttributeNS(null, "width", "80");
	okBox.setAttributeNS(null, "height", "40");
	okBox.setAttributeNS(null, "rx", "5");
	okBox.setAttributeNS(null, "ry", "5");
	okBox.setAttributeNS(null, "fill", "blue");
	
	var okText = document.createElementNS(svgns, "text");
	okEle.appendChild(okText);

	okText.setAttributeNS(null, "font-family", "Arial");
	okText.setAttributeNS(null, "font-size", "30");
	okText.setAttributeNS(null, "fill", window.textColor);
	okText.setAttributeNS(null, "x", "15");
	okText.setAttributeNS(null, "y", "30");
	okText.appendChild(document.createTextNode("OK"));

	which.svgEle.appendChild(okEle);

	okEle.addEventListener("click", function(){
		var dealer = (window.player.id === which.dealer ? window.player : window.opponent);
		var dealerScore = data["handPoints"][dealer.id];
		which.statusMessage((dealer.id === window.player.id ? "Your" : (dealer.username + "'s")) + " hand scores "+dealerScore+" points.");
		which.scoreboard.addPoints(dealer.id, dealerScore);
		var coordinates = {
			x: (dealer.id === window.player.id ? window.coordinates.playerHand.x : window.coordinates.opponentHand.x) + 50,
			y: (dealer.id === window.player.id ? window.coordinates.playerHand.y : window.coordinates.opponentHand.y) + 60
		};

		okEle.setAttributeNS(null, "transform", "translate("+coordinates.x+","+coordinates.y+")");

		okEle.removeEventListener("click", arguments.callee);
		okEle.addEventListener("click", function(){
			var cribScore = data["handPoints"]["crib"];
			which.statusMessage((dealer.id === window.player.id ? "Your" : (dealer.username + "'s")) + " crib scores "+cribScore+" points.");
			which.scoreboard.addPoints(dealer.id, cribScore);
			var coordinates = {
				x: (dealer.id === window.player.id ? window.coordinates.myCrib.x : window.coordinates.opponentCrib.x) + 50,
				y: (dealer.id === window.player.id ? window.coordinates.myCrib.y : window.coordinates.opponentCrib.y) + 60
			};

			okEle.setAttributeNS(null, "transform", "translate("+coordinates.x+","+coordinates.y+")");

			okEle.removeEventListener("click", arguments.callee);
			okEle.addEventListener("click", function(){


				okEle.removeEventListener("click", arguments.callee);
				okEle.parentNode.removeChild(okEle);

				// Tell the server we're done looking
				ajaxCall(
					"post",
					{
						application: "game",
						method: "doneViewingHands",
						data: {
							gameID: window.gameID
						}
					},
					function(data){
						data = data["game"];
						if(data["error"] !== undefined){
							which.statusMessage(data["error"] + " Refreshing page.");
							setTimeout(function(){window.history.go(0);}, 1000);
						}else{
							if(data["gamestate"] === "DEALING"){
								which.gamestate = "DEALING";
								which.resetGamespace();
								which.constructState();
							}else{
								which.statusMessage("Waiting for "+window.opponent.username+" to finish looking at the hands.");
								var interval = setInterval(function(){
									ajaxCall(
										"get",
										{
											application: "game",
											method: "getGameState",
											data: {
												gameID: window.gameID
											}
										},
										function(data){
											if(data["game"]["gamestate"] === "DEALING"){
												clearInterval(interval);
												which.gamestate = "DEALING";
												which.resetGamespace();
												which.constructState();
											}
										}
									);
								}, 2000);
							}
						}
					}
				);

			}, false);

		}, false);
	},false);
}

Gamespace.prototype.resetGamespace = function(){
	this.gamestate = "DEALING";

	// Clear all objects, clear out the DOM
	this.dealer = (this.dealer === window.player.id ? window.opponent.id : window.player.id);
	
	this.cutCard = {suit:null, number:null};
	this.deck.updateCutCard(new PlayingCard());

	this.playedCards.clearFromScreen();
	this.playedCards.cards = [];
	this.hands[window.player.id].clear();
	this.hands[window.opponent.id].clear();

	this.crib.clear();
	this.crib.setDealer(this.dealer);

}

Gamespace.prototype.declareWinner = function(winnerID){
	// Stop the game - there is a winner!
	this.gamestatus = "FINISHED";

	// Announce the win
	if(winnerID === window.player.id){
		this.statusMessage("Congratulations, you won!");
	}else{
		this.statusMessage("Unfortunately " + window.opponent.username + " beat you. Better luck next time!");
	}

	switch(this.gamestate){
		case "DEALING":
		case "CHOOSING_CRIB":
			// Never should have a winner in this state, since no points should be given out.
			// Reload the page
			this.statusMessage("Something went wrong. Reloading the page...");
			setTimeout(function(){window.history.go(0);}, 1500);

			break;
		case "CUTTING_CARD":

			// Winner if cut card is jack. No polling should be happening, since the polling would have stopped when the card is cut.
			break;
		case "PEGGING":

			// Stop the polling, if polling is happening.
			// Stop cards from being draggable

			break;
		case "VIEWING_HANDS":
		case "WAITING_PLAYER_1":
		case "WAITING_PLAYER_2":

			// Let continue viewing hands, but the scoreboard won't let any more points being added.

			break;
		default:
			// Just throw them out to the lobby
			alert("Uh oh! Something went wrong! The game doesn't know what state it is in. Try reopening the game.");
			window.close();
			break;
	}
}

window.coordinates = {
	playerHand: {x:100, y:525},
	opponentHand: {x:100, y:55},
	deck: {x: 720, y: 280},
	myCrib: {x: 580, y: 500},
	opponentCrib: {x:580, y:20},
	playedCards: {x: 50, y: 350}
}