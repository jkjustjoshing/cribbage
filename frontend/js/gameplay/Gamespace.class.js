function Gamespace(data, svgEle){
	
	// Initialize values
	this.gamestate = data.gamestate;
	this.gamestatus = data.gamestatus;

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

	this.cutCard = data.cutCard; // TODO create the card object, or set to undefined, pass to deck object??

	this.dealer = data.dealer;

	this.playedCards = data.playedCards // TODO create the PlayedCards object

	this.hands = [];
	this.hands[window.player.id] = new PlayerHand(data.hands[window.player.id], svgEle, window.coordinates.playerHand);
	this.hands[window.player.id].sort();
	this.hands[window.opponent.id] = new PlayerHand(data.hands[window.opponent.id], svgEle, window.coordinates.opponentHand); 
	console.log("crib");
	console.log(data.hands["crib"]);
	this.crib = new Crib(data.hands["crib"], svgEle, (this.dealer === window.player.id ? window.coordinates.myCrib : window.coordinates.opponentCrib), this.dealer);


	this.deck = new CardDeck(svgEle, this.cutCard);
	this.deck.setDealer(this.dealer, this.gamestate);
	
	this.turn = data.turn;

	// End Initialize values

	
	// Run the game
	this.constructState();


}

/**
 * Takes the current state and puts the proper listeners on
 * everything and propogates the message to the other objects.
 * The previous state must be cleaned up before this method is called.
 */
Gamespace.prototype.constructState = function(){
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
			}
			// If dealing, listeners will be set up by the deck object
			else{

			}
			break;
		case "CHOOSING_CRIB":
			// Set event listeners on my cards
			which.crib.choosingCribMode(true);
			which.statusMessage("Drag 2 cards into the crib from your hand.");
			which.hands[window.player.id].chooseCrib();

			// Poll for other player submitting crib cards to update view
			// Once crib has 4 cards move to next state
			break;
		case "CUTTING_CARD":
			// If not dealing spread cards and set listener on each card
			// On click of card, send cut information to server
			which.crib.choosingCribMode(false);
			break;
		case "PEGGING":
			// Setup using another function, very in-depth
			break;
		case "VIEWING_HANDS":
			// Create button to mark being done
			// Confirm each user's set of points, add to board
			// Once all points have been confirmed, send server the ready message
			// Move to appropriate stage based on the returned new state
			break
		case "WAITING_PLAYER_1":
			// Not yet sure how to tell between this and WAITING_PLAYER_2
			// Show message, poll server for being ready to move on to next state.
			break;
		case "WAITING_PLAYER_2":

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

window.coordinates = {
	playerHand: {x:100, y:525},
	opponentHand: {x:100, y:55},
	deck: {x: 720, y: 280},
	myCrib: {x: 580, y: 500},
	opponentCrib: {x:580, y:20},
	playedCards:0
}