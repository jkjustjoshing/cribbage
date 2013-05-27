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
function PlayerHand(cardArray, container, coordinates){	
	this.cards = [];
	this.ele = container;

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

		which.sort(true);
		return true;
	}else{
		which.sort(true);
		return false;
	}
}

