/**
 * SVG Namespace global variable
 * @type {String}
 */
var svgns = "http://www.w3.org/2000/svg";

var xlinkns = "http://www.w3.org/1999/xlink";
//var xhtmlns = "http://www.w3.org/1999/xhtml";


function PlayedCards(cards, container, coordinates){
	this.cards = cards;
	this.screenCards = [];
	this.coordinates = coordinates;
	this.container = container;
	this.count = 0;
	for(var i = 0; i < cards.length; ++i){
		this.count += cards[i].number;
		if(this.count > 31){
			this.count = cards[i].number;
		}
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

	this.screenCards[this.screenCards.length] = card;
	this.cards[this.cards.length] = card;
	this.container.appendChild(card.ele);
	card.ele.setAttributeNS(null, "transform", "translate("+x+", "+y+")");
	this.count += card.getCount();
	this.updateCountText();
}

PlayedCards.prototype.clearFromScreen = function(){
	for(var i = 0; i < this.screenCards.length; ++i){
		this.container.removeChild(this.screenCards[i].ele);
	}
	this.screenCards = [];
}