/**
 * SVG Namespace global variable
 * @type {String}
 */
var svgns = "http://www.w3.org/2000/svg";

var xlinkns = "http://www.w3.org/1999/xlink";
//var xhtmlns = "http://www.w3.org/1999/xhtml";


function PlayedCards(cards, container, coordinates){
	this.cards = [];
	this.screenCards = [];
	this.coordinates = coordinates;
	this.container = container;
	this.count = 0;

	// Put a background on the screen
	this.background = document.createElementNS(svgns, "rect");
	this.background.setAttributeNS(null, "width", "500");
	this.background.setAttributeNS(null, "height", "200");
	this.background.setAttributeNS(null, "rx", "10");
	this.background.setAttributeNS(null, "ry", "10");
	this.background.setAttributeNS(null, "x", "100");
	this.background.setAttributeNS(null, "y", (coordinates.y-100));
	this.background.setAttributeNS(null, "fill", "#627D7F");
	this.container.appendChild(this.background);


	// the play() method adds cards to these object properties, so this is just for temporary use in this function
	var count = 0;
	var screenCards = [];
	for(var i = 0; i < cards.length; ++i){
		if(cards[i].suit !== null){
			count += cards[i].number;
			screenCards[screenCards.length] = {card: new PlayingCard(cards[i].number, cards[i].suit), playedByID: cards[i].playedByID};
		}else{
			for(var j = 0; j < screenCards.length; ++j){
				this.cards[this.cards.length] = screenCards[j];
			}
			this.cards[this.cards.length] = {card: new PlayingCard, playedByID: cards[i].playedByID};
			count = 0;
			screenCards = [];
		}
	}

	for(var i = 0; i < screenCards.length; ++i){
		this.play(screenCards[i].card, screenCards[i].playedByID, true);
	}

	this.updateCountText();

}

PlayedCards.prototype.updateCountText = function(nullIfReset){

	if(nullIfReset === null){
		if(this.countEle !== undefined){
			this.countEle.parentNode.removeChild(this.countEle);
			this.countEle = undefined;
		}
	}else if(this.countEle === undefined){
		this.countEle = document.createElementNS(svgns, "g");
		this.countEle.setAttributeNS(null, "transform", "translate("+this.coordinates.x+", "+this.coordinates.y+")")
		var text = document.createElementNS(svgns, "text");
		text.setAttributeNS(null, "fill", window.textColor);
		text.appendChild(document.createTextNode("Count:"));
		this.countText = document.createElementNS(svgns, "text");
		this.countText.setAttributeNS(null, "transform", "translate("+15+", "+20+")");
		this.countText.setAttributeNS(null, "fill", window.textColor);
		this.countText.appendChild(document.createTextNode("" + this.count));

		this.countEle.appendChild(text);
		this.countEle.appendChild(this.countText);
		this.container.appendChild(this.countEle);
	}else{
		this.countText.firstChild.nodeValue = ""+this.count;
	}
}

/**
 * Adds a card to the screen.
 * @param  {PlayingCard} card   The card to play
 * @param  {int} player The ID of the player who played this
 */
PlayedCards.prototype.play = function(card, player, initializing){
	if(!(card instanceof PlayingCard) && card !== null){
		throw 'Can only "play" a PlayingCard object';
	}
	var x = this.coordinates.x + 100;
	var y = this.coordinates.y - 80;
	if(window.player.id === player){
		y += 20;
	}
	x += this.screenCards.length * 35;

	if(card !== null){
		this.container.appendChild(card.ele);

		if(card.ele.parentNode === null){
			// Card not yet on page, just show
			card.ele.setAttributeNS(null, "transform", "translate("+x+", "+y+")");
		}else{
			// Card on page - animate to position
			$(card.ele).animate({
				svgTransform: "translate("+x+", "+y+")"
			}, 100);
		}
		
		// Add to object
		this.screenCards[this.screenCards.length] = {card: card, playedByID: player};
		this.cards[this.cards.length] = {card: card, playedByID: player};
		this.count += card.getCount();

		this.updateCountText();
	}else if(initializing === true){
		this.cards[this.cards.length] = {card: new PlayingCard(), playedByID: player};
	}

	if(initializing === undefined || initializing == false){
		if(card !== null){
			card.loading(true);
		}else{
			card = {number: 0, suit: ""};
		}
		var which = this;
		ajaxCall(
			"post",
			{
				application: "game",
				method: "playCard",
				data: {
					gameID: window.gameID,
					card: {
						number: card.number,
						suit: card.suit
					}
				}
			},
			function(data){
				if(card.number !== 0){
					card.loading(false);
				}
				if(data["game"]["success"] !== true){
					window.gamespace.statusMessage(data["game"]["error"]);
					if(card.number !== 0){
						which.count -= card.getCount();
						which.updateCountText();

						window.gamespace.hands[window.player.id].add(card);
						window.gamespace.hands[window.player.id].sort(true);

						which.screenCards.length--;
					}
					which.cards.length--;
				}else{

					if(card.dragHandler !== undefined){
						card.dragHandler.removeTarget({
							target: window.gamespace.playedCards.background
						});
					}

					// Success, update
					window.gamespace.setTurn(data["game"]["turn"]);

					// If it was a go, clear the cards
					if(card.number === 0 && data["game"]["playedCards"][data["game"]["playedCards"].length-1]["number"] === 0){
						window.gamespace.playedCards.play(null, data["game"]["playedCards"][data["game"]["playedCards"].length-1]["playedByID"], true);
						which.clearFromScreen();
					}


					var scoreboard = window.gamespace.scoreboard;

					// If the scores changed update them on the scoreboard
					if(scoreboard.playerInfo[window.player.id].score !== data["game"]["scores"][window.player.id]){
						// Player has a new score
						if(scoreboard.playerInfo[window.player.id].score !== data["game"]["backPinPositions"][window.player.id]){
							scoreboard.changeScore(window.player.id, data["game"]["backPinPositions"][window.player.id]);
						}
						scoreboard.changeScore(window.player.id, data["game"]["scores"][window.player.id]);					
					}
					if(scoreboard.playerInfo[window.opponent.id].score !== data["game"]["scores"][window.opponent.id]){
						// Opponent has a new score
						if(scoreboard.playerInfo[window.opponent.id].score !== data["game"]["backPinPositions"][window.opponent.id]){
							changeScore(window.opponent.id, data["game"]["backPinPositions"][window.opponent.id]);
						}
						scoreboard.changeScore(window.opponent.id, data["game"]["scores"][window.opponent.id]);					
					}
					// end updating scores
					
					// If the player "go"ed, show it!
					

					if(window.gamespace.gamestate !== data["game"]["gamestate"]){
						window.gamespace.gamestate = data["game"]["gamestate"];
						window.gamespace.constructState();
					}
				}
			}
		);
	}
}

PlayedCards.prototype.clearFromScreen = function(){
	for(var i = 0; i < this.screenCards.length; ++i){
		this.container.removeChild(this.screenCards[i].card.ele);
	}
	this.screenCards = [];
	this.count = 0;
	this.updateCountText();
}

PlayedCards.prototype.successfulDrag = function(x, y){
	if(window.gamespace.turn != window.player.id) return false;
	var bbox = this.background.getBBox();
	var insideHorizontally = x > bbox.x && x < (bbox.x + bbox.width);
	var insideVertically = y > bbox.y && y < (bbox.y + bbox.height);
	return insideHorizontally && insideVertically;
};

PlayedCards.prototype.startPolling = function(){
	var which = this;
	var interval = setInterval(function(){
		which.poll(interval);
	}, 200);
}

PlayedCards.prototype.poll = function(intervalToClearWhenStateChanges){
	var which = window.gamespace.playedCards;
	ajaxCall(
		"get",
		{
			application: "game",
			method: "getPlayedCards",
			data: {
				gameID: window.gameID
			}
		},
		function(data){
			if(data["game"]["error"] !== undefined){
				console.log(data["game"]["error"]);
				//window.history.go(0);
			}else{
				var playedCards = data["game"]["playedCards"];
				// array of cards = data["game"]

				for(var i = which.cards.length ; i < playedCards.length; ++i){
					if(playedCards[i]["number"] === 0){
						window.gamespace.playedCards.play(null, playedCards[i]["playedByID"], true);
						which.clearFromScreen();
					}else{
						var anonymousCard = window.gamespace.hands[playedCards[i]["playedByID"]].remove(new PlayingCard());
						anonymousCard.ele.parentNode.removeChild(anonymousCard.ele);

						var card = new PlayingCard(playedCards[i]["number"], playedCards[i]["suit"]);
						window.gamespace.hands[playedCards[i]["playedByID"]].add(card, true); // true for "do not animate"
						window.gamespace.hands[playedCards[i]["playedByID"]].remove(card);
						window.gamespace.playedCards.play(card, playedCards[i]["playedByID"], true);
					}
				}

				if(window.gamespace.scoreboard.playerInfo[window.player.id].score !== data["game"]["scores"][window.player.id]){
					// Player has a new score
					if(window.gamespace.scoreboard.playerInfo[window.player.id].score !== data["game"]["backPinPositions"][window.player.id]){
						window.gamespace.scoreboard.changeScore(window.player.id, data["game"]["backPinPositions"][window.player.id]);
					}
					window.gamespace.scoreboard.changeScore(window.player.id, data["game"]["scores"][window.player.id]);					
				}
				if(window.gamespace.scoreboard.playerInfo[window.opponent.id].score !== data["game"]["scores"][window.opponent.id]){
					// Opponent has a new score
					if(window.gamespace.scoreboard.playerInfo[window.opponent.id].score !== data["game"]["backPinPositions"][window.opponent.id]){
						window.gamespace.scoreboard.changeScore(window.opponent.id, data["game"]["backPinPositions"][window.opponent.id]);
					}
					window.gamespace.scoreboard.changeScore(window.opponent.id, data["game"]["scores"][window.opponent.id]);					
				}

				if(window.gamespace.gamestate !== data["game"]["gamestate"] || window.gamespace.gamestate === "VIEWING_HANDS" 
					|| window.gamespace.gamestate.indexOf("WAITING_PLAYER") !== -1){
					window.gamespace.gamestate = data["game"]["gamestate"];
					// Stop from updating whose turn it is
					clearInterval(intervalToClearWhenStateChanges);
					window.gamespace.constructState();
				}else{
					window.gamespace.setTurn(data["game"]["turn"]);
				}

			}
		}
	);
}