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
function Crib(cardArray, container, coordinates){	
	this.cards = [];
	this.ele = container;
	this.isChoosingCribMode = false;

	this.coordinates = coordinates;

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

	this.padding = {x:10, y:5};
	this.coordinates.x += this.padding.x;
	this.coordinates.y += this.padding.y;
	this.textBoxCoordinates = {};
	this.textBoxCoordinates[window.player.id] = {x: 700, y:670};
	this.textBoxCoordinates[window.opponent.id] = {x: 660, y:45};


} 

/**
 * Adds a card to the crib
 * @param  {PlayingCard} card The card to add to the crib
 * @return {Boolean}      Whether or not the card was added
 */
Crib.prototype.add = function(card){
	var which = this;

	if(this.cards.length < 4){
		this.cards[this.cards.length] = card;

		// Animate card to the crib
		$(card.ele).animate({
			svgTransform: "translate("+(this.coordinates.x + (this.cards.length-1)*35)+","+this.coordinates.y+")"
		}, 100);

		// Make card draggable outside the crib
		if(card.isVisible()){
			/*card.drag(function(ele, x, y){
				// Where is the card? If it's out of the crib move it.
				if(!window.gamespace.crib.successfulDrag(x+50, y+70)){ //half the width, half the height of a card
					// It's not in the crib - send it over!
					var cardID = ele.getAttributeNS(null, "name").split("|");

					var card = which.remove(new PlayingCard(cardID[0], cardID[1]), false);
					window.gamespace.hands[window.player.id].add(card);

					// 		If there are 2 cards there show button
					// If it's not in crib animate back, then sort
					which.sort(true);
					return true;
				}else{
					which.sort(true);
					return false;
				}
			});*/
		}

		this.confirmSelection();

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
					svgTransform: "translate("+(this.coordinates.x + i*35)+","+this.coordinates.y+")"
				}, 400);
			}else{
				this.cards[i].ele.setAttributeNS(null, "transform", "translate("+(this.coordinates.x + i*35)+","+this.coordinates.y+")");
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
	alert("f");
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
		if(dealerID === window.player.id){
			text.appendChild(document.createTextNode("My Crib"));
		}else{
			text.appendChild(document.createTextNode( window.opponent.username + "'s Crib"));
		}
		this.cribBox.appendChild(text);
		this.ele.appendChild(this.cribBox);
	}

	// Put the crib box either above or below the deck, indicating the dealer
	if(dealerID === window.player.id){
		// Put it below the deck
		this.cribBox.childNodes[0].setAttributeNS(null, "y", this.coordinates.y - this.padding.y);
		this.cribBox.childNodes[0].setAttributeNS(null, "x", this.coordinates.x - this.padding.x);
		this.cribBox.childNodes[1].setAttributeNS(null, "x", this.textBoxCoordinates[window.player.id].x);
		this.cribBox.childNodes[1].setAttributeNS(null, "y", this.textBoxCoordinates[window.player.id].y);
	}else{
		// Put it above the deck
		this.cribBox.childNodes[0].setAttributeNS(null, "y", this.coordinates.y - this.padding.y);
		this.cribBox.childNodes[0].setAttributeNS(null, "x", this.coordinates.x - this.padding.x);
		this.cribBox.childNodes[1].setAttributeNS(null, "x", this.textBoxCoordinates[window.opponent.id].x);
		this.cribBox.childNodes[1].setAttributeNS(null, "y", this.textBoxCoordinates[window.opponent.id].y);
	}
}

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
				this.cards[i].drag(false);
			}
		}
		this.confirmSelection();
	}else{
		this.cribBox.childNodes[0].setAttributeNS(null, "stroke", window.textColor);
	}
}

/**
 * Is a card dragged to the passed (x,y) coordinate inside the crib box?
 * @param  {int} x The x coordinate for the card being dragged
 * @param  {int} y The y coordinate for the card being dragged
 * @return {boolean}   Whether or not the card was inside the crib box
 */
Crib.prototype.successfulDrag = function(x, y){
	if(this.cards.length >= 2) return false;
	var bbox = this.cribBox.childNodes[0].getBBox();
	var insideHorizontally = x > bbox.x && x < (bbox.x + bbox.width);
	var insideVertically = y > bbox.y && y < (bbox.y + bbox.height);
	return insideHorizontally && insideVertically;

}

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
	

	if(this.confirmCrib === undefined){
		var which = this;
		this.confirmCrib = function(){
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
					console.log("crib sent");
					console.log(data);
				}
			);

			// 		Callback - set up interval to check for gamestate change
			// 		When gamestate changes tell the gamestate object to setup the next state
		};
	}

	if(visibleCards.length !== 2){
		this.cribBox.childNodes[0].setAttributeNS(null, "stroke", "red");
		cribText.removeChild(cribText.firstChild);
		cribText.appendChild(document.createTextNode(cribTextString));
		cribText.removeEventListener("click", this.confirmCrib); console.log("This line doesn't work!!!!");
	}else{
		this.cribBox.childNodes[0].setAttributeNS(null, "stroke", "green");
		cribText.removeChild(cribText.firstChild);
		cribText.appendChild(document.createTextNode("Click to confirm"));
		cribText.addEventListener("click", this.confirmCrib, false);
	}
}