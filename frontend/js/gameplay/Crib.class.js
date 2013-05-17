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

Crib.prototype.add = function(card){
	this.cards[this.cards.length] = card;

	var cardContainer = document.createElementNS(svgns, "g");
	cardContainer.setAttributeNS(null, "transform", "translate("+(this.coordinates.x + (this.cards.length-1)*35)+","+this.coordinates.y+")");
	cardContainer.appendChild(card.ele);
	this.ele.appendChild(cardContainer);

}
Crib.prototype.sort = PlayerHand.prototype.sort;

Crib.prototype.setDealer = function(dealerID, gamestate){
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
			text.appendChild(document.createTextNode("Opponent's Crib"));
		}
		this.cribBox.appendChild(text);
		this.ele.appendChild(this.cribBox);
	}

	// Put the crib box either above or below the deck, indicating the dealer
	if(dealerID === window.player.id){
		// Put it below the deck
		this.cribBox.childNodes[0].setAttributeNS(null, "y", window.coordinates.myCrib.y);
		this.cribBox.childNodes[0].setAttributeNS(null, "x", window.coordinates.myCrib.x);
		this.cribBox.childNodes[1].setAttributeNS(null, "x", "700");
		this.cribBox.childNodes[1].setAttributeNS(null, "y", "670");
	}else{
		// Put it above the deck
		this.cribBox.childNodes[0].setAttributeNS(null, "y", window.coordinates.opponentCrib.y);
		this.cribBox.childNodes[0].setAttributeNS(null, "x", window.coordinates.opponentCrib.x);
		this.cribBox.childNodes[1].setAttributeNS(null, "x", "660");
		this.cribBox.childNodes[1].setAttributeNS(null, "y", "45");
	}
}

/**
 * Is a card dragged to the passed (x,y) coordinate inside the crib box?
 * @param  {int} x The x coordinate for the card being dragged
 * @param  {int} y The y coordinate for the card being dragged
 * @return {boolean}   Whether or not the card was inside the crib box
 */
Crib.prototype.successfulDrag = function(x, y){
	var bbox = this.cribBox.childNodes[0].getBBox();
	var insideHorizontally = x > bbox.x && x < (bbox.x + bbox.width);
	var insideVertically = y > bbox.y && y < (bbox.y + bbox.height);
	return insideHorizontally && insideVertically;

}