function PlayingCard(suit, number){
	
	// A suit of "" is an anonymous card (cards the user con't identify)
	if(["diamond", "club", "heart", "spade", ""].indexOf(suit) !== 1){
		this.suit = suit;
	}else{
		console.log("Can't create PlayingCard with a suit of " + suit);
	}
	
	// A number of 0 is an anonymous card (cards the user can't identify)
	if(this.number < 0 || this.number > 13){
		console.log("Can't create PlayingCard with a number of " + number);
	}else{
		this.number = number;
	}
	
	var number0 = this.number == 0;
	var string0 = this.suit == "";
	if((number0 && !string0) || (!number0 && string0)){
		console.log("Can't create Playing card with a " + (number0?"number":"suit") + " of " + (number0?this.number:this.suit));
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
	return (this.number == 0 && this.suit == "");
}