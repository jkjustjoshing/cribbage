/**
 * SVG Namespace global variable
 * @type {String}
 */
var svgns = "http://www.w3.org/2000/svg";

/**
 * PlayerHand object
 * Represents a Player's hand, both in memory and on the screen in the DOM
 * 
 * @param Array  cardArray An array of PlayingCard objects
 */
function PlayerHand(cardArray, container, coordinates, state){	
	this.cards = [];
	this.ele = container;

	this.coordinates = coordinates;

	for(var i = 0; i < cardArray.length; ++i){
		if(!(cardArray[i] instanceof PlayingCard)){
			if(cardArray[i]['inHand'] || cardArray[i]["inHand"] === null || state === "VIEWING_HANDS" || state === "WAITING_PLAYER_1" || state === "WAITING_PLAYER_2"){
				var card = new PlayingCard(cardArray[i]["number"], cardArray[i]["suit"]);
				this.add(card, true);
			}
		}else{
			this.add(cardArray[i], true);
		}
	}

} 

PlayerHand.prototype.remove = function(card){
	var oldLength = this.cards.length;
	for(var i = this.cards.length-1; i >= 0; --i){
		if(this.cards[i].equals(card)){
			var card = this.cards[i];
			this.cards.splice(i, 1);
			return card;
		}
	}
	return false;
}

/**
 * Adds a card to the data structure and to the hand DOM.
 * @param  PlayingCard card          The PlayingCard to add
 * @param  boolean doNotAnimate      Place the card where it goes instead of animating
 */
PlayerHand.prototype.add = function(card, doNotAnimate){
	this.cards[this.cards.length] = card;

	if(doNotAnimate === true){
		card.ele.setAttributeNS(null, "transform", "translate("+(this.coordinates.x + (this.cards.length-1)*35)+","+this.coordinates.y+")");
		this.ele.appendChild(card.ele);

	}else{
		if(card.ele.parentNode === null){
			// Not in DOM yet - put it there
			card.ele.setAttributeNS(null, "transform", "translate("+(window.coordinates.deck.x+10)+", "+window.coordinates.deck.y+")")
			this.ele.appendChild(card.ele);
		}

		this.sort(true);

	}

	if(this.dragging){
		var which = this;
		card.drag(function(ele, x, y){
			return which.draggingCallback(ele, x, y, which);
		})
	}
}

/**
 * Sorts the cards in the hand both in memory and on the screen
 */
PlayerHand.prototype.sort = function(animate){
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

/**
 * Sets the proper event listeners and dragging areas
 * to choose cards to put into the crib
 */
PlayerHand.prototype.chooseCrib = function(disable){
	// Set global drag/mousup listeners (this.ele is the SVG element containing the hand)
	if(disable === undefined || disable === true){
		var which = this;
		this.dragging = true;
		// Set local mousedown listener
		for(var i = 0; i < this.cards.length; ++i){
			this.cards[i].drag(function(ele, x, y){
				return which.cribDraggingCallback(ele, x, y, which);
			});
		}
	}else{
		this.dragging = false;
		for(var i = 0; i < this.cards.length; ++i){
			this.cards[i].drag(false);
		}
	}
}


// Where is the card? If it's in the crib move it.
PlayerHand.prototype.cribDraggingCallback = function(ele, x, y, which){
	if(window.gamespace.crib.successfulDrag(x+50, y+70)){ //half the width, half the height of a card
		
		// It's in the crib - send it over!
		var cardID = ele.getAttributeNS(null, "name").split("|");

		var card = which.remove(new PlayingCard(cardID[0], cardID[1]), false);
		window.gamespace.crib.add(card);

		// If there are 2 cards there show button
		// If it's not in crib animate back, then sort
		which.sort(true);
		return true;
	}else{
		which.sort(true);
		return false;
	}
}

PlayerHand.prototype.peggingMode = function(disable){
	// Set global drag/mousup listeners (this.ele is the SVG element containing the hand)
	if(disable === undefined || disable === true){
		var which = this;
		this.dragging = true;
		// Set local mousedown listener
		for(var i = 0; i < this.cards.length; ++i){
			this.cards[i].drag(function(ele, x, y){
				return which.peggingDraggingCallback(ele, x, y, which);
			});
		}

		// Make "go" button
		this.goEle = document.createElementNS(svgns, "g");
		var goBox = document.createElementNS(svgns, "rect");
		this.goEle.appendChild(goBox);
		this.goEle.setAttributeNS(null, "transform", "translate(47, 585)");

		goBox.setAttributeNS(null, "width", "40");
		goBox.setAttributeNS(null, "height", "20");
		goBox.setAttributeNS(null, "rx", "5");
		goBox.setAttributeNS(null, "ry", "5");
		goBox.setAttributeNS(null, "fill", "blue");
		
		var goText = document.createElementNS(svgns, "text");
		this.goEle.appendChild(goText);

		goText.setAttributeNS(null, "font-family", "Arial");
		goText.setAttributeNS(null, "font-size", "20");
		goText.setAttributeNS(null, "fill", window.textColor);
		goText.setAttributeNS(null, "x", "6");
		goText.setAttributeNS(null, "y", "17");
		goText.appendChild(document.createTextNode("Go"));

		this.ele.appendChild(this.goEle);

		this.goEle.addEventListener("click", function(){
			if(window.gamespace.turn !== window.player.id){
				return;
			}else{
				window.gamespace.playedCards.play(null, window.player.id);
			}
		});

	}else{
		this.dragging = false;
		for(var i = 0; i < this.cards.length; ++i){
			this.cards[i].drag(false);
		}
	}
}

// Where is the card? If it's in the crib move it.
PlayerHand.prototype.peggingDraggingCallback = function(ele, x, y, which){
	if(window.gamespace.playedCards.successfulDrag(x+50, y+70)){ //half the width, half the height of a card
		
		// It's in the crib - send it over!
		var cardID = ele.getAttributeNS(null, "name").split("|");

		var card = which.remove(new PlayingCard(cardID[0], cardID[1]), false);
		window.gamespace.playedCards.play(card, window.player.id);
		card.drag(false);
		
		which.sort(true);
		return true;
	}else{
		which.sort(true);
		return false;
	}
}

PlayerHand.prototype.clear = function(){
	var cards = this.cards.slice(0); // Clone the array, so the length stays constant as we remove cards
	for(var i = 0; i < cards.length; ++i){
		var card = this.remove(cards[i]);
		card.ele.parentNode.removeChild(card.ele);
	}
}

