/**
 * SVG Namespace global variable
 * @type {String}
 */
var svgns = "http://www.w3.org/2000/svg";

var xlinkns = "http://www.w3.org/1999/xlink";
//var xhtmlns = "http://www.w3.org/1999/xhtml";

/**
 * PlayingCard object
 * Represents a playing card, both in memory and on the screen in the DOM.
 * Initialize with no arguments if it's an unknown card value.
 * 
 * @param {string} suit The suit of the card - either "diamond", "club", "heart", or "spade"
 * @param {int} number The number of the card: 1-13
 */
function PlayingCard(number, suit){

	this.suit = suit;
	this.number = number;
	if(this.suit === "diamond" || this.suit === "heart"){
		this.color = "red";
	}else if(this.suit === "club" || this.suit === "spade"){
		this.color = "black";
	}else{
		this.suit = undefined;
	}

	if(this.number == 0){
		this.number = undefined;
	}

	this.ele = document.createElementNS(svgns, "g");
	if(this.suit === undefined || this.number === undefined){
		this.ele.setAttributeNS(null, "name", "hidden");
	}else{
		this.ele.setAttributeNS(null, "name", this.number + "|" + this.suit);
	}
	var rect = document.createElementNS(svgns, "rect");
	rect.setAttributeNS(null, "width", "100");
	rect.setAttributeNS(null, "height", "140");
	rect.setAttributeNS(null, "rx", "8");
	rect.setAttributeNS(null, "ry", "8");
	rect.setAttributeNS(null, "stroke", "black");
	rect.setAttributeNS(null, "stroke-width", "1");
	rect.setAttributeNS(null, "fill", "white");
	this.ele.appendChild(rect);

	if(this.suit === undefined || this.number === undefined){
		// Unknown card, set ele as unknown card ele
		var gradientDef = document.createElementNS(svgns, "defs");
		var gradient = document.createElementNS(svgns, "radialGradient");
		gradient.setAttributeNS(null, "id", "unknownGradient");
		gradient.setAttributeNS(null, "gradientUnits", "objectBoundingBox");
		gradient.setAttributeNS(null, "cx", "0.5");
		gradient.setAttributeNS(null, "cy", "0.5");
		gradientDef.appendChild(gradient);

		var stop1 = document.createElementNS(svgns, "stop");
		stop1.setAttributeNS(null, "offset", "0%");
		stop1.setAttributeNS(null, "stop-color", "#66ff66");
		
		var stop2 = document.createElementNS(svgns, "stop");
		stop2.setAttributeNS(null, "offset", "30%");
		stop2.setAttributeNS(null, "stop-color", "#66ff66");
		
		var stop3 = document.createElementNS(svgns, "stop");
		stop3.setAttributeNS(null, "offset", "90%");
		stop3.setAttributeNS(null, "stop-color", "#66cc88");
		
		gradient.appendChild(stop1);
		gradient.appendChild(stop2);
		gradient.appendChild(stop3);

		this.ele.insertBefore(gradientDef, rect);
		rect.setAttributeNS(null, "fill", "url(#unknownGradient)");

	}else{
		// Card known, set ele as known card ele
		var text = document.createElementNS(svgns, "text");
		text.setAttributeNS(null, "id", this.number + "|" + this.suit);
		text.setAttributeNS(null, "x", "5");
		text.setAttributeNS(null, "y", "20");
		text.setAttributeNS(null, "font-family", "Arial");
		text.setAttributeNS(null, "font-size", "20");
		text.setAttributeNS(null, "fill", this.color);

		var cardNumber = this.number;
		if(cardNumber === 1) cardNumber = "A";
		else if(cardNumber === 11) cardNumber = "J";
		else if(cardNumber === 12) cardNumber = "Q";
		else if(cardNumber === 13) cardNumber = "K";

		text.appendChild(document.createTextNode(cardNumber));
		this.ele.appendChild(text);

		var use = document.createElementNS(svgns, "use");
		use.setAttributeNS(xlinkns, "xlink:href", "#"+this.number+"|"+this.suit);
		use.setAttributeNS(null, "transform", "rotate(180,50,70)");
		this.ele.appendChild(use);

		var path = document.createElementNS(svgns, "path");
		path.setAttributeNS(null, "id", "symbol"+"|"+this.number+"|"+this.suit);
		if(this.suit == "club"){
			path.setAttributeNS(null, "d", "M 20,70 " +
	                   " Q 28,60 27,50 "+
	                   " C -8,70 -8,17 20,27 " +
	
	                   " C -8,-8 68,-8  40,27 " +

	                   " C 68,17 68,70 33,50 " +
	                   " Q 32,60 40,70 ");
		}else if(this.suit == "heart"){
			path.setAttributeNS(null, "d", "M 33,70 " +
	                     "L 3,25 " + 
						 "C -10,0 28,-10 33,13 " + 
						 "C 38,-10 76,0 63,25 " +
						 "L 33,70");
		}else if(this.suit == "spade"){
			path.setAttributeNS(null, "d", "M 20,70 "+ 
							 "Q 28,60 27,50 "+
							 
							 "C -13,70 -7,23 30,0 "+
							 
							 "C 67,23 73,70 33,50 "+
							 "Q 32,60 40,70");
		}else if(this.suit == "diamond"){
			path.setAttributeNS(null, "d", "M 30,0 " +
						   "Q 16.95,18.25 0,35 "+
						   "Q 16.95,51.75 30,70 "+
						   "Q 43.05,51.75 60,35 "+
			               "Q 43.05,18.25 30,0");
		}
		path.setAttributeNS(null, "transform", "translate(35,50) scale(0.5)");
		path.setAttributeNS(null, "fill", this.color);
		this.ele.appendChild(path);

		var usePath = document.createElementNS(svgns, "use");
		usePath.setAttributeNS(xlinkns, "xlink:href", "#symbol|"+this.number+"|"+this.suit);
		usePath.setAttributeNS(null, "transform", "translate(-15, 0) scale(0.5)");
		usePath.setAttributeNS(null, "id", "duplicateSymbol|"+this.number+"|"+this.suit);
		this.ele.appendChild(usePath);

		var usePath = document.createElementNS(svgns, "use");
		usePath.setAttributeNS(xlinkns, "xlink:href", "#duplicateSymbol|"+this.number+"|"+this.suit);
		usePath.setAttributeNS(null, "transform", "rotate(180,50,70)");
		this.ele.appendChild(usePath);

	}
}


PlayingCard.prototype.equals = function(other){
	if(!(other instanceof PlayingCard)){
		return false;
	}
	
	return (this.number == other.number && this.suit == other.suit);
}


PlayingCard.prototype.getCount = function(){
	if(this.number < 11){
		return this.number;
	}else{
		return 10;
	}
};

PlayingCard.prototype.isVisible = function(){
	return !(this.number === undefined || this.suit === undefined);
};

PlayingCard.prototype.isOnScreen = function(){
	return this.ele.parentNode !== null;
}

PlayingCard.prototype.loading = function(enable){
	if(this.isLoading === undefined){
		this.isLoading = false;
	}

	if(enable && !this.isLoading){
		// Show loading bar
		this.isLoading = true;
		this.loadingEle = document.createElementNS(svgns, "rect");
		this.loadingEle.setAttributeNS(null, "width", "100");
		this.loadingEle.setAttributeNS(null, "height", "140");
		this.loadingEle.setAttributeNS(null, "rx", "8");
		this.loadingEle.setAttributeNS(null, "ry", "8");
		this.loadingEle.setAttributeNS(null, "fill", "black");
		this.loadingEle.setAttributeNS(null, "opacity", "0.5");

		this.ele.appendChild(this.loadingEle);

	}else if(!enable && this.isLoading){
		// Hide loading bar
		this.isLoading = false;

		this.ele.removeChild(this.loadingEle);

	}
}