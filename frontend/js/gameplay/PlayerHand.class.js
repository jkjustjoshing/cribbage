/**
 * PlayerHand object
 * Represents a Player's hand, both in memory and on the screen in the DOM
 * 
 * @param {Boolean} isCrib
 * @param {Array}  cardArray
 */
function PlayerHand(isCrib, cardArray){
	this.isCrib = isCrib;
	this.cards = [];
	
	for(var i = 0; i < cardArray.length; ++i){
		if(cardArray[i] instanceof PlayerCard){
			this.cards[this.cards.length] = cardArray[i];
		}else{
			// Array taken from an ajax call. Implement if needed.
			this.cards[this.cards.length] = new PlayerCard(0,""); //TODO anonymous card for now
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