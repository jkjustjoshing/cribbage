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
	this.background.setAttributeNS(null, "y", (coordinates.y-120));
	this.background.setAttributeNS(null, "fill", "#444");
	this.container.appendChild(this.background);


	// the play() method adds cards to these object properties, so this is just for temporary use in this function
	var count = 0;
	var screenCards = [];
	for(var i = 0; i < cards.length; ++i){
		if(cards[i].suit !== null){
			count += cards[i].number;
			screenCards[screenCards.length] = {card: new PlayingCard(cards[i].number, cards[i].suit), playedByID: cards[i].playedByID};
		}else{
			count = 0;
			screenCards = [];
		}
	}

	for(var i = 0; i < screenCards.length; ++i){
		this.play(screenCards[i].card, screenCards[i].playedByID, true);
	}

	this.updateCountText();

}

PlayedCards.prototype.updateCountText = function(){

	if(this.countEle === undefined){
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
	var x = this.coordinates.x + 100;
	var y = this.coordinates.y - 100;
	if(window.player.id === player){
		y += 20;
	}
	x += this.screenCards.length * 35;

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

	if(initializing === undefined || initializing == false){
		card.loading(true);
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
				card.loading(false);
				if(data["game"]["success"] !== true){
					window.gamespace.statusMessage(data["game"]["error"]);
					which.count -= card.getCount();
					which.updateCountText();

					window.gamespace.hands[window.player.id].add(card);

					which.screenCards.length--;
					which.cards.length--;
				}else{
					// Success, update
					window.gamespace.turn = data["game"]["turn"];

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

					if(window.gamespace.gamestate !== data["game"]["gamestate"]){
						alert("Gamestate is now " + data["game"]["gamestate"]);
						window.gamespace.gamestate = data["game"]["gamestate"];
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
}

PlayedCards.prototype.successfulDrag = function(x, y){
	if(window.gamespace.turn != window.player.id) return false;
	var bbox = this.background.getBBox();
	var insideHorizontally = x > bbox.x && x < (bbox.x + bbox.width);
	var insideVertically = y > bbox.y && y < (bbox.y + bbox.height);
	return insideHorizontally && insideVertically;
};

PlayedCards.prototype.poll = function(){
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

				var i;
				if(which.screenCards.length === 0){
					i = 0;
				}else{
					var lastCardKnown = which.screenCards[which.screenCards.length-1];
					for(i = 0; i < playedCards.length; ++i){
						if(playedCards[i]["suit"] === lastCardKnown.card.suit && playedCards[i]["number"] === lastCardKnown.card.number){
							++i;
							break;
						}
					}
				}

				for( ; i < playedCards.length; ++i){
					var anonymousCard = window.gamespace.hands[playedCards[i]["playedByID"]].remove(new PlayingCard());
					anonymousCard.ele.parentNode.removeChild(anonymousCard.ele);

					var card = new PlayingCard(playedCards[i]["number"], playedCards[i]["suit"]);
					window.gamespace.hands[playedCards[i]["playedByID"]].add(card, true); // true for "do not animate"
					window.gamespace.hands[playedCards[i]["playedByID"]].remove(card);
					window.gamespace.playedCards.play(card, playedCards[i]["playedByID"], true);

				}

				window.gamespace.turn = data["game"]["turn"];

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

				if(window.gamespace.gamestate !== data["game"]["gamestate"]){
					alert("Gamestate is now " + data["game"]["gamestate"]);
					window.gamespace.gamestate = data["game"]["gamestate"];
				}

			}
		}
	);
}