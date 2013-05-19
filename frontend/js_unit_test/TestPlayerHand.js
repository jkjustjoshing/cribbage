test("TestRemove", function(){
	
	var cardArr = [];
	for(var i = 0; i < 6; ++i){
		cardArr[i] = new PlayingCard(i+2, "club");
	}

	var svgEle = document.createElementNS("http://www.w3.org/2000/svg", "svg");

	var hand = new PlayerHand(cardArr, svgEle, {x:0, y:0});


	equal(hand.cards.length, 6, "New Playerhand should have 6 cards");

	hand.remove(cardArr[0]);

	equal(hand.cards.length, 5, "New Playerhand should have 5 cards");

	hand.remove(cardArr[1]);

	equal(hand.cards.length, 4, "New Playerhand should have 5 cards");

	hand.remove(cardArr[5]);

	equal(hand.cards.length, 3, "New Playerhand should have 5 cards");

	hand.remove(cardArr[3]);

	equal(hand.cards.length, 2, "New Playerhand should have 5 cards");

	hand.remove(cardArr[4]);

	equal(hand.cards.length, 1, "New Playerhand should have 5 cards");

});