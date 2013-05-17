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
				console.log(cardArray[i]);
				var card = new PlayingCard(cardArray[i]["number"], cardArray[i]["suit"]);
				this.add(card, true);
			}
		}else{
			this.add(cardArray[i], true);
		}
	}

} 

PlayerHand.prototype.remove = function(card){
	for(var i = 0; i < this.cards.length; ++i){
		if(this.cards[i].equals(card)){
			this.cards.splice(i);
			return true;
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


	var cardContainer = document.createElementNS(svgns, "g");
	cardContainer.setAttributeNS(null, "name", this.cards.length-1);

	if(doNotAnimate === true){
		cardContainer.setAttributeNS(null, "transform", "translate("+(this.coordinates.x + (this.cards.length-1)*35)+","+this.coordinates.y+")");
		console.log(card);
		cardContainer.appendChild(card.ele);
		this.ele.appendChild(cardContainer);

	}else{
		if(card.ele.parentNode === null){
			// Not in DOM yet - put it there
			cardContainer.setAttributeNS(null, "transform", "translate("+(window.coordinates.deck.x+10)+", "+window.coordinates.deck.y+")")
			cardContainer.appendChild(card.ele);
			this.ele.appendChild(cardContainer);
		}

		var which = this;
		$(card.ele.parentNode).animate({
			svgTransform: "translate("+(which.coordinates.x + (which.cards.length-1)*35)+","+which.coordinates.y+")"
		}, 400);
	}
}

/**
 * Sorts the cards in the hand both in memory and on the screen
 */
PlayerHand.prototype.sort = function(){
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
			this.cards[i].ele.parentNode.setAttributeNS(null, "transform", "translate("+(this.coordinates.x + i*35)+","+this.coordinates.y+")");
			this.cards[i].ele.parentNode.setAttributeNS(null, "name", i);
			if(i !== 0){
				this.cards[i].ele.parentNode.parentNode.insertBefore(this.cards[i].ele.parentNode, this.cards[i-1].ele.parentNode.nextSibling);
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
PlayerHand.prototype.chooseCrib = function(){
	// Set global drag/mousup listeners (this.ele is the SVG element containing the hand)
	var which = this;

	// Set local mousedown listener
	for(var i = 0; i < this.cards.length; ++i){
		this.cards[i].ele.parentNode.addEventListener("mousedown", function(mousedownTarget){
			var target = mousedownTarget.target;
			while(target.nodeName.toLowerCase() != "g" || target.parentNode.nodeName.toLowerCase() != "g"){
				target = target.parentNode;
			}
			target = target.parentNode; // Get the top <g> tag, not the 2nd one

			target.parentNode.appendChild(target);

			console.log("once");
			var transform = target.getAttributeNS(null, "transform");
			var initXString = transform.substring("translate(".length, transform.indexOf(","));
			var initYString = " " + transform.substring(transform.indexOf(",")+1, transform.indexOf(")"));

			var initX = parseInt(initXString) - mousedownTarget.clientX;
			var initY = parseInt(initYString) - mousedownTarget.clientY;
			var x, y;
			var mousemove = function(mousemoveTarget){
				x = mousemoveTarget.clientX  + initX;
				y = mousemoveTarget.clientY + initY;
				target.setAttributeNS(null, "transform", "translate("+x+", "+y+")");

			};

			var mouseup = function(){
				which.ele.removeEventListener("mousemove", mousemove);
				which.ele.removeEventListener("mouseup", mouseup);

				// Where is the card? If it's in the crib move it.
				if(window.gamespace.crib.successfulDrag(x+50, y+70)){ //half the width, half the height of a card
					// It's in the crib - send it over!
					var cardID = parseInt(target.getAttributeNS(null, "name"));
					console.log(which.cards);
					console.log(cardID);
					console.log(which.cards[cardID]);
					window.gamespace.crib.add(which.cards[cardID]);
					which.remove(which.cards[cardID]);
					//which.sort();

					// 		If there are 2 cards there show button
					// If it's not in crib animate back, then sort

				}else{
					// Not in the crib - spring back
					target.setAttributeNS(null, "transform", "translate("+parseInt(initXString)+", "+parseInt(initYString)+")");
				}
			};

			which.ele.addEventListener('mouseup',mouseup,false);
			which.ele.addEventListener('mousemove',mousemove,false);
		}, false);
	}
}

/**
 * Plays one of the cards to the PlayedCards object.
 * This method returns the card and ele and the caller
 * must not drop the card. This method will tell
 * the server via ajax about the move, but only
 * if the card is NOT anonymous.
 * @param  [PlayingCard or DOMNode] card The card being played
 * @return [PlayingCard and DOMNode]     The card being played
 */
PlayerHand.prototype.playCard = function(card){
	// Find the card
	for(var i = 0; i < this.cards.length; ++i){
		if(this.cards[i].equals(card)){
			break;
		}
	}
	if(i == this.cards.length){
		alert("That card can't be played!.");
		return;
	}

	// pull the card out
	var foundCard = this.cards[i];
	foundCard.ele.parentNode.removeChild(foundCard.ele);
	this.cards[i] = undefined;

	// Shift the other cards over to the left
	this.sort()

	// If the card isn't anonymous send the play via ajax
	console.log("Must tell server about card being played")
		// Tell the caller about send on success of callback, or immediately if anonymous
}