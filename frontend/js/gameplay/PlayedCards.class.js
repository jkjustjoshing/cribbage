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
	this.background.setAttributeNS(null, "fill", "#444");
	this.background.setAttributeNS(null, "transform", "translate(100, "+(coordinates.y-120)+")");
	this.container.appendChild(this.background);


	// the play() method adds cards to these object properties, so this is just for temporary use in this function
	var count = 0;
	var screenCards = [];
	for(var i = 0; i < cards.length; ++i){
		count += cards[i].number;

		if(count > 31){
			count = cards[i].number;
			screenCards = [];
		}
		screenCards[screenCards.length] = {card: new PlayingCard(cards[i].number, cards[i].suit), playedByID: cards[i].playedByID};
	}

	for(var i = 0; i < screenCards.length; ++i){
		this.play(screenCards[i].card, screenCards[i].playedByID);
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
PlayedCards.prototype.play = function(card, player){
	var x = this.coordinates.x + 100;
	var y = this.coordinates.y - 100;
	if(window.player.id === player){
		y += 20;
	}
	x += this.screenCards.length * 35;

	this.container.appendChild(card.ele);
	card.ele.setAttributeNS(null, "transform", "translate("+x+", "+y+")");
	this.updateCountText();

	// Add to object
		this.screenCards[this.screenCards.length] = {card: card, playedByID: player};
		this.cards[this.cards.length] = {card: card, playedByID: player};
		this.count += card.getCount();
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