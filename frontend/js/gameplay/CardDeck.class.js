/**
 * SVG Namespace global variable
 * @type {String}
 */
var svgns = "http://www.w3.org/2000/svg";
/**
 * Represents a deck of cards in memory and in the DOM
 * @param {DOMEle} container The SVG element to hold the deck
 * @param {PlayingCard} cutCard   The card to display as the cut card. Omit for none
 */
function CardDeck(container, cutCard){	
	this.ele = document.createElementNS(svgns, "g");
	this.ele.setAttributeNS(null, "transform", "translate("+window.coordinates.deck.x+", "+window.coordinates.deck.y+")");
	this.cutCard = cutCard;
	if(container !== undefined){
		container.appendChild(this.ele);
	}

	var which = this;

	// Add the cards to the screen, with a delay between displaying each one
	var alt_i = 0;
	var cards = [];
	for(var i = 0; i < this.deckHeight; ++i){
		if(i == 6 && which.cutCard !== undefined && which.cutCard !== null){
			// Display the cut card
			cards[i] = document.createElementNS(svgns, "g");
			cards[i].setAttributeNS(null, "transform", "translate("+(i*2)+",0)");
			cards[i].appendChild(cutCard.ele);
			setTimeout(function(){which.ele.appendChild(cards[alt_i]);++alt_i;}, (i*60));
		}else{
			cards[i] = document.createElementNS(svgns, "g");
			cards[i].setAttributeNS(null, "transform", "translate("+(i*2)+",0)");
			cards[i].appendChild((new PlayingCard().ele));
			setTimeout(function(){which.ele.appendChild(cards[alt_i]);++alt_i;}, (i*60));
		}
	}

	this.numberOfTimesToShuffle = 1;

} 

CardDeck.prototype.deckHeight = 7;

/**
 * Reshuffle the deck multiple times. Will send to database once dealing
 */
CardDeck.prototype.reshuffle = function(){
	++this.numberOfTimesToShuffle;
}

CardDeck.prototype.setDealer = function(dealerID, gamestate){
	// If there is no dealer element create it
	if(this.dealer === undefined){
		this.dealer = document.createElementNS(svgns, "text");
		this.dealer.setAttributeNS(null, "font-family", "Arial");
		this.dealer.setAttributeNS(null, "font-size", "20");
		this.dealer.setAttributeNS(null, "fill", window.textColor);
		this.dealer.appendChild(document.createTextNode("Dealer"));
		this.ele.appendChild(this.dealer);
	}

	// Put the dealer element either above or below the deck, indicating the dealer
	// If the current user is the dealer, set 
	if(dealerID == window.player.id){
		// Put below deck. 
		this.dealer.setAttributeNS(null, "transform", "translate(25,170)");
		
		// If the current player should deal, setup the appropriate listeners
		if(gamestate === "DEALING" && this.dealButton !== true){
			// Put text above the deck that says "Click deck to deal"
			var text = document.createElementNS(svgns, "text");
			text.setAttributeNS(null, "font-family", "Arial");
			text.setAttributeNS(null, "font-size", "12");
			text.setAttributeNS(null, "fill", window.textColor);
			text.appendChild(document.createTextNode("Click deck to deal"));
			text.setAttributeNS(null, "transform", "translate(8,-8)");
			this.ele.appendChild(text);

			// Onclick of deck remove text, tell the server to shuffle 4 times
			var which = this;
			this.dealButton = true;
			this.ele.addEventListener("click", function(evt){
				text.parentNode.removeChild(text);
				this.dealButton = false;
				this.removeEventListener("click", arguments.callee);
				which.numberOfTimesToShuffle = 4;
				which.deal();
			});
		}
	}else{
		// Put above deck
		this.dealer.setAttributeNS(null, "transform", "translate(25,-18)");
	}

}

/**
 * Deal the cards in the database, get the results and put in the CardHands
 * @return {Array} 2d array of card values to put into the CardHands
 */
CardDeck.prototype.deal = function(){
	var which = this;

	ajaxCall(
		"post",
		{
			application: "game",
			method: "deal",
			data:{
				gameID: window.gameID,
				numberOfTimesToShuffle: which.numberOfTimesToShuffle
			}
		},
		function(data){
			data = data["game"];
			if(data["success"]){
				window.gamespace.animateDeal();
			}else{
				alert(data["error"]);
			}
		}
	);
}

CardDeck.prototype.updateCutCard = function(card){
	this.cutCard = card;
	this.ele.removeChild(this.ele.lastChild);
	cutCard = document.createElementNS(svgns, "g");
	cutCard.setAttributeNS(null, "transform", "translate("+((this.deckHeight-1)*2)+",0)");
	cutCard.appendChild(card.ele);
	this.ele.appendChild(cutCard);
}
