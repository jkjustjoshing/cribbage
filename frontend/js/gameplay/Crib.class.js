/**
 * SVG Namespace global variable
 * @type {String}
 */
var svgns = "http://www.w3.org/2000/svg";

/**
 * Crib object
 * Represents the crib, both in memory and on the screen in the DOM.
 * Inherits from PlayerHand
 * 
 * @param Array  cardArray An array of PlayingCard objects
 */
function Crib(cardArray, container, coordinates, dealer){	
	this.cards = [];
	this.ele = container;
	this.isChoosingCribMode = false;

	this.coordinates = coordinates;
	this.masterCoordinates = coordinates;

	this.padding = {x:10, y:5};
	this.textBoxCoordinates = {};
	this.textBoxCoordinates[window.player.id] = {x: 120, y: 170};
	this.textBoxCoordinates[window.opponent.id] = {x: 90, y: 170};

	this.setDealer(dealer);

	for(var i = 0; i < cardArray.length; ++i){
		if(!(cardArray[i] instanceof PlayingCard)){
			if(cardArray[i]['inHand']){
				var card = new PlayingCard(cardArray[i]["number"], cardArray[i]["suit"]);
				this.add(card, true);
			}
		}else{
			this.add(cardArray[i], true);
		}
	}

} 

/**
 * Adds a card to the crib
 * @param  {PlayingCard} card The card to add to the crib
 * @return {Boolean}      Whether or not the card was added
 */
Crib.prototype.add = function(card, dragged, dontAnimate){
	var which = this;

	if(this.cards.length < 4){
		this.cards[this.cards.length] = card;

		if(!card.isOnScreen()){
			this.ele.appendChild(card.ele);
		}
		// Animate card to the crib
		if(dontAnimate){
			card.ele.setAttributeNS(null, "transform", "translate("+(this.coordinates[this.dealer].x + (this.cards.length-1)*35)+","+this.coordinates[this.dealer].y+")");
		}else{
			$(card.ele).animate({
				svgTransform: "translate("+(this.coordinates[this.dealer].x + (this.cards.length-1)*35)+","+this.coordinates[this.dealer].y+")"
			}, 100);
		}

		if(dragged !== false){
			this.confirmSelection();
		}

		return true;
	}
	return false;
}

/**
 * Sort the crib in the same way a PlayerHand gets sorted
 * @type {Function}
 */
Crib.prototype.sort = function(animate){
	this.cards.sort(function(a, b){
		if(a === undefined){
			return 1;
		}else if(b === undefined){
			return -1;
		}
		return a.number - b.number;
	});

	var numUndefined = 0;
	for(var i = 0; i < this.cards.length; ++i){
		if(this.cards[i] !== undefined){
			if(animate === true){
				$(this.cards[i].ele).animate({
					svgTransform: "translate("+(this.coordinates[this.dealer].x + i*35)+","+this.coordinates[this.dealer].y+")"
				}, 400);
			}else{
				this.cards[i].ele.setAttributeNS(null, "transform", "translate("+(this.coordinates[this.dealer].x + i*35)+","+this.coordinates[this.dealer].y+")");
			}

			if(i !== 0){
				this.cards[i].ele.parentNode.insertBefore(this.cards[i].ele, this.cards[i-1].ele.nextSibling);
			}
		}else{
			numUndefined++;
		}
	}

	this.cards.length -= numUndefined;
}

Crib.prototype.cardRemove = PlayerHand.prototype.remove;
Crib.prototype.remove = function(card){
	var card = this.cardRemove(card);
	this.confirmSelection();

	return card;
}

/**
 * Sets who the current dealer is.
 * @param  {[type]} dealerID  [description]
 * @param  {[type]} gamestate [description]
 * @return {[type]}           [description]
 */
Crib.prototype.setDealer = function(dealerID){
	this.dealer = dealerID;

	var cribMessage;
	if(dealerID === window.player.id){
		cribMessage = "My Crib";
	}else{
		cribMessage = window.opponent.username + "'s Crib";
	}

	// If there is no cribBox element create it
	if(this.cribBox === undefined){
		this.cribBox = document.createElementNS(svgns, "g");
		var rect = document.createElementNS(svgns, "rect");
		rect.setAttributeNS(null, "width", "300");
		rect.setAttributeNS(null, "height", "180");
		rect.setAttributeNS(null, "rx", "10");
		rect.setAttributeNS(null, "ry", "10");
		rect.setAttributeNS(null, "stroke", window.textColor);
		rect.setAttributeNS(null, "stroke-width", "2");
		rect.setAttributeNS(null, "fill", "none");
		this.cribBox.appendChild(rect);

		var text = document.createElementNS(svgns, "text");
		text.setAttributeNS(null, "font-family", "Arial");
		text.setAttributeNS(null, "font-size", "20");
		text.setAttributeNS(null, "fill", window.textColor);
		text.appendChild(document.createTextNode(cribMessage));
		
		this.cribBox.appendChild(text);
		this.ele.appendChild(this.cribBox);
	}
	
	this.cribBox.setAttributeNS(null, "transform", "translate("+this.masterCoordinates[dealerID].x+", "+this.masterCoordinates[dealerID].y+")");
	this.cribBox.childNodes[1].setAttributeNS(null, "x", this.textBoxCoordinates[dealerID].x);
	this.cribBox.childNodes[1].setAttributeNS(null, "y", this.textBoxCoordinates[dealerID].y);
	this.cribBox.childNodes[1].firstChild.nodeValue = cribMessage;

};

/**
 * Tells the crib whether or not it should be accepting changes to the crib
 * @param  {Boolean} isChoosingCribMode If the current mode is choosing crib or not
 */
Crib.prototype.choosingCribMode = function(isChoosingCribMode){
	this.isChoosingCribMode = isChoosingCribMode;

	// If there are cards in the crib and we're going out
	// of choosing mode, make them not draggable
	if(this.isChoosingCribMode === false){
		for(var i = 0; i < this.cards.length; ++i){
			if(this.cards[i].isVisible()){
				this.cards[i].dragHandler.removeAllTargets();
			}
		}
		this.confirmSelection();
	}else{
		this.cribBox.childNodes[0].setAttributeNS(null, "stroke", window.textColor);
	}
}

Crib.prototype.hide = function(){
	for(var i = 0; i < this.cards.length; ++i){
		if(this.cards[i].isVisible()){
			var card = this.cards[i];
			this.cards[i] = new PlayingCard();
			card.ele.parentNode.replaceChild(this.cards[i].ele, card.ele);
		}
	}
};

Crib.prototype.confirmSelection = function(){
	var visibleCards = [];
	for(var i = 0; i < this.cards.length; ++i){
		if(this.cards[i].isVisible()){
			visibleCards[visibleCards.length] = this.cards[i];
		}
	}

	var cribText = this.cribBox.childNodes[1];
	var cribTextString;
	if(this.dealer === window.player.id){
		cribTextString = "My Crib";

	}else{
		cribTextString = window.opponent.username + "'s Crib";
	}

	if(visibleCards.length !== 2){
		this.cribBox.childNodes[0].setAttributeNS(null, "stroke", "red");
		cribText.removeChild(cribText.firstChild);
		cribText.appendChild(document.createTextNode(cribTextString));
		cribText.removeEventListener("click", this.confirmCrib);
	}else{
		this.cribBox.childNodes[0].setAttributeNS(null, "stroke", "green");
		cribText.firstChild.nodeValue = "Click to confirm";
		cribText.addEventListener("click", this.confirmCrib, false);
	}
};

Crib.prototype.confirmCrib = function(){
	var which = window.gamespace.crib;
	// Tell the player hand to disable dragging
	window.gamespace.hands[window.player.id].chooseCrib(false);

	// Disable dragging myself, change back text of crib
	which.choosingCribMode(false);
	
	var visibleCards = [];
	for(var i = 0; i < which.cards.length; ++i){
		if(which.cards[i].isVisible()){
			visibleCards[visibleCards.length] = which.cards[i];
		}
	}

	// Send the crib info to the server
	ajaxCall(
		"post",
		{
			application: "game",
			method: "putInCrib",
			data: {
				gameID: window.gameID,
				cards: [
					{
						suit: visibleCards[0].suit,
						number: visibleCards[0].number
					},
					{
						suit: visibleCards[1].suit,
						number: visibleCards[1].number
					}
				]
			}
		},
		function(data){
			data = data["game"];

			if(data.success === true){
				which.hide();
				which.sort();
				window.gamespace.statusMessage("Waiting for " + window.opponent.username + " to put 2 cards in the crib.");
				which.cribBox.childNodes[1].removeEventListener("click", which.confirmCrib);
				which.cribBox.childNodes[1].firstChild.nodeValue = (which.dealer === window.player.id ? "My Crib" : (window.opponent.username + "'s Crib"));

			}else{
				alert(data.error);
			}
		}
	);

	// 		Callback - set up interval to check for gamestate change
	// 		When gamestate changes tell the gamestate object to setup the next state
};

Crib.prototype.clear = PlayerHand.prototype.clear;

Crib.prototype.viewingCards = function(cardArray){
	// Remove all cards from screen
	var cards = this.cards.slice(0);
	for(var i = cards.length-1; i >= 0; --i){
		var card = this.cardRemove(cards[i]); //Only removes from memory
		card.ele.parentNode.removeChild(card.ele);
	}

	// Display new set of cards
	for(var i = 0; i < cardArray.length; ++i){
		this.add(new PlayingCard(cardArray[i].number, cardArray[i].suit), false, true);
	}
}
