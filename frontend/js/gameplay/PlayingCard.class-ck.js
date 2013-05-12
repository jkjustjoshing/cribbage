/**
 * PlayingCard object
 * Represents a playing card, both in memory and on the screen in the DOM.
 * Initialize with no arguments if it's an unknown card value.
 * 
 * @param {string} suit The suit of the card - either "diamond", "club", "heart", or "spade"
 * @param {int} number The number of the card: 1-13
 */
function PlayingCard(suit, number){
	
	this.suit = suit;
	this.number = number;
	if(this.suit == "diamond" || this.suit == "heart"){
		this.color = "red";
	}else if(this.suit == "club" || this.suit == "spade"){
		this.color = "black";
	}

	this.ele = document.createElementNS(svgns, "g");

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

	}else{
		// Card known, set ele as known card ele
		var text = document.createElementNS(svgns, "text");
		text.setAttributeNS(null, "id", this.number + "|" + this.suit);
		text.setAttributeNS(null, "x", "5");
		text.setAttributeNS(null, "y", "20");
		text.setAttributeNS(null, "font-family", "Arial");
		text.setAttributeNS(null, "font-size", "20");
		text.setAttributeNS(null, "fill", this.color);
		text.appendChild(document.createTextNode(this.number));
		this.ele.appendChild(text);

		var use = document.createElementNS(svgns, "use");
		use.setAttributeNS(xlinkns, "xlink:href", "#"+this.number+"|"+this.suit);
		use.setAttributeNS(null, "transform", "rotate(180,50,70)");
		this.ele.appendChild(use);

		var path = document.createElementNS(svgns, "path");
		path.setAttributeNS(null, "id", "symbol"+"|"+this.number+"|"+this.suit);
		if(this.suit == "club"){
			path.setAttributeNS(null, "d", "M 20,70 
	                    Q 28,60 27,50 
	                    C -8,70 -8,17 20,27
	
	                    C -8,-8 68,-8  40,27 

	                    C 68,17 68,70 33,50
	                    Q 32,60 40,70");
		}else if(this.suit == "heart"){
			path.setAttributeNS(null, "d", "M 33,70 
	                     L 3,25 
						 C -10,0 28,-10 33,13 
						 C 38,-10 76,0 63,25 
						 L 33,70");
		}else if(this.suit == "spade"){
			path.setAttributeNS(null, "d", "M 20,70 
							 Q 28,60 27,50 
							 
							 C -13,70 -7,23 30,0
							 
							 C 67,23 73,70 33,50
							 Q 32,60 40,70");
		}else if(this.suit == "diamond"){
			path.setAttributeNS(null, "d", "M 30,0 
						   Q 16.95,18.25 0,35 
						   Q 16.95,51.75 30,70 
						   Q 43.05,51.75 60,35 
						   Q 43.05,18.25 30,0");
		}
		path.setAttributeNS(null, "transform", "translate(42.5,61.25) scale(0.25)");
		path.setAttributeNS(null, "fill", this.color);



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
}

PlayingCard.prototype.getSVG = function(){
	return 1;
}

PlayingCard.prototype.isVisible = function(){
	return (this.number === undefined || this.suit === undefined);
}