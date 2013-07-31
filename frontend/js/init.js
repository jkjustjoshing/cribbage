/**
 * Initialize the game from document load. All
 * document ready code should go here related 
 * to the game (not the chat).
 */
$(document).ready(function(){

	// Get the gameInfo
	ajaxCall("get",
		{
			application: "game",
			method: "getGameData",
			data: {
				gameID: window.gameID
			}
		},
		function(data){
			data = data["game"];
			if(data["gamestatus"] != "IN_PROGRESS"){
				// Make better in the future. Right now just stops game from forming
				alert("This game is over!");
				return;
			}

			window.gamespace = new Gamespace(data, document.getElementsByTagName("svg")[1]);

			window.gamespace.constructState();

		}
	);


//EXAMPLE ONLY, DELETE
/*	$(document).ready(function(){
	var cardArray = [
		new PlayingCard(5, "spade"),
		new PlayingCard(5, "heart"),
		new PlayingCard(3, "spade"),
		new PlayingCard(2, "diamond")
	];
	console.log(cardArray);
	window.hand = new PlayerHand(cardArray, document.getElementsByTagName("svg")[1]);
	window.hand.sort();
	var cardArray = [
		new PlayingCard(),
		new PlayingCard(),
		new PlayingCard(),
		new PlayingCard()
	];
	console.log(cardArray);
	window.hand2 = new PlayerHand(cardArray, document.getElementsByTagName("svg")[1]);
	window.hand2.ele.setAttributeNS(null, "transform", "translate(0, 100)");
});
*/

});