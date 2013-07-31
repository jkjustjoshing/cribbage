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
	this.scoreboard = new Scoreboard(playerInfo, opponentInfo);

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

	var which = this;
	switch(which.gamestate){
		case "DEALING":
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
			break;
		case "CHOOSING_CRIB":
			// Set event listeners on my cards
			which.crib.choosingCribMode(true);
			which.statusMessage("Drag 2 cards into the crib from your hand.");
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

							// Make the second card go to the hand 0.5 seconds after the first
							setTimeout(function(){
								var card = which.hands[window.opponent.id].remove(new PlayingCard());
								which.crib.add(card);

								which.crib.sort();

								if(data["game"]["crib"].length == 4){
									// If the crib has 4 cards stop the polling
									clearInterval(interval);

									// Move to cutting card state
									which.gamestate = "CUTTING_CARD";
									which.constructState();
								}
							}, 500);
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
				setTimeout(function(){
					var cutCardIndex = prompt("Pick a cut card");
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
				}, 2000);
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
			which.statusMessage("");
			which.hands[window.player.id].peggingMode(true);
			var interval = setInterval(which.playedCards.poll, 2000);
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
			alert("Invalid state, Gamespace.class");
			window.location("lobby.php");
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
		this.turnTriangles[window.player.id] = triangle.cloneNode();
		this.turnTriangles[window.player.id].setAttributeNS(null, "transform", "translate(70,380) rotate(180,0,0)");
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
		this.turnTriangles[window.player.id].setAttributeNS(null, "fill", falseColor);
		this.turnTriangles[window.opponent.id].setAttributeNS(null, "fill", falseColor);
	}
}

Gamespace.prototype.viewHands = function(needToConfirmHand){
	var which = this;
	which.setTurn(false); // Remove the turn indicator triangle
	
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
			for(var i = 0; i < data["hands"][window.player.id].length; ++i){
				data["hands"][window.player.id][i].inHand = 1;
			}
			var newPlayerHand = new PlayerHand(data["hands"][window.player.id], playerHand.ele, playerHand.coordinates);
			playerHand.clear();
			newPlayerHand.sort();
			which.hands[window.player.id] = newPlayerHand;

			
			var opponentHand = which.hands[window.opponent.id];
			for(var i = 0; i < data["hands"][window.opponent.id].length; ++i){
				data["hands"][window.opponent.id][i].inHand = 1;
			}
			var newOpponentHand = new PlayerHand(data["hands"][window.opponent.id], opponentHand.ele, opponentHand.coordinates);
			opponentHand.clear();
			newOpponentHand.sort();
			which.hands[window.opponent.id] = newOpponentHand;
			
			which.crib.viewingCards(data["hands"]["crib"]);
			which.crib.sort();

			if(needToConfirmHand){
				which.confirmHandView(data);
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

	var goEle = document.createElementNS(svgns, "g");
	var goBox = document.createElementNS(svgns, "rect");
	goEle.appendChild(goBox);
	
	var coordinates = {
		x: (nonDealer.id === window.player.id ? window.coordinates.playerHand.x : window.coordinates.opponentHand.x) + 50,
		y: (nonDealer.id === window.player.id ? window.coordinates.playerHand.y : window.coordinates.opponentHand.y) + 60
	};

	goEle.setAttributeNS(null, "transform", "translate("+coordinates.x+","+coordinates.y+")");

	goBox.setAttributeNS(null, "width", "80");
	goBox.setAttributeNS(null, "height", "40");
	goBox.setAttributeNS(null, "rx", "5");
	goBox.setAttributeNS(null, "ry", "5");
	goBox.setAttributeNS(null, "fill", "blue");
	
	var goText = document.createElementNS(svgns, "text");
	goEle.appendChild(goText);

	goText.setAttributeNS(null, "font-family", "Arial");
	goText.setAttributeNS(null, "font-size", "30");
	goText.setAttributeNS(null, "fill", window.textColor);
	goText.setAttributeNS(null, "x", "15");
	goText.setAttributeNS(null, "y", "30");
	goText.appendChild(document.createTextNode("OK"));

	which.svgEle.appendChild(goEle);

	goEle.addEventListener("click", function(){
		var dealer = (window.player.id === which.dealer ? window.player : window.opponent);
		var dealerScore = data["handPoints"][dealer.id];
		which.statusMessage((dealer.id === window.player.id ? "Your" : (dealer.username + "'s")) + " hand scores "+dealerScore+" points.");
		which.scoreboard.addPoints(dealer.id, dealerScore);
		var coordinates = {
			x: (dealer.id === window.player.id ? window.coordinates.playerHand.x : window.coordinates.opponentHand.x) + 50,
			y: (dealer.id === window.player.id ? window.coordinates.playerHand.y : window.coordinates.opponentHand.y) + 60
		};

		goEle.setAttributeNS(null, "transform", "translate("+coordinates.x+","+coordinates.y+")");

		goEle.removeEventListener("click", arguments.callee);
		goEle.addEventListener("click", function(){
			var cribScore = data["handPoints"]["crib"];
			which.statusMessage((dealer.id === window.player.id ? "Your" : (dealer.username + "'s")) + " crib scores "+cribScore+" points.");
			which.scoreboard.addPoints(dealer.id, cribScore);
			var coordinates = {
				x: (dealer.id === window.player.id ? window.coordinates.myCrib.x : window.coordinates.opponentCrib.x) + 50,
				y: (dealer.id === window.player.id ? window.coordinates.myCrib.y : window.coordinates.opponentCrib.y) + 60
			};

			goEle.setAttributeNS(null, "transform", "translate("+coordinates.x+","+coordinates.y+")");

			goEle.removeEventListener("click", arguments.callee);
			goEle.addEventListener("click", function(){


				goEle.removeEventListener("click", arguments.callee);
				goEle.parentNode.removeChild(goEle);

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

	this.playedCards.clearFromScreen();
	this.playedCards.cards = [];
	this.hands[window.player.id].clear();
	this.hands[window.opponent.id].clear();

	this.crib.clear();
	this.crib.setDealer(this.dealer);



}

window.coordinates = {
	playerHand: {x:100, y:525},
	opponentHand: {x:100, y:55},
	deck: {x: 720, y: 280},
	myCrib: {x: 580, y: 500},
	opponentCrib: {x:580, y:20},
	playedCards: {x: 50, y: 350}
}