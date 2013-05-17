function Challenge(challenger, opponent, status){
	
	// Boolean - am I the challenger
	this.challenger = challenger;
	
	this.opponentID = opponent.id;
	this.opponentUsername = opponent.username;
	this.status = status;
	this.gameID = -1;
	
}



Challenge.prototype.setStatus = function(status){
	this.status = status;
}

Challenge.prototype.getMessage = function(){
	
	var opponentUsername = this.opponentUsername;

	switch(this.status){
		case "PENDING":
			var str = "";
			if(this.challenger){
				str += "Waiting for " + opponentUsername + " to respond to your challenge...";
				str += '<a href="javascript:window.chats['+this.opponentID+'].challenge.selectNewStatus(\'CANCELLED\');">Cancel</a>';
			}else{
				str += opponentUsername + ' has challenged you to a game! <a href="javascript:window.chats['+this.opponentID+'].challenge.selectNewStatus(\'ACCEPTED\');">Accept</a> <a href="javascript:window.chats['+this.opponentID+'].challenge.selectNewStatus(\'DENIED\');">Decline</a>';
			}
			return str;
		case "VIEWED":
			if(this.challenger){
				return opponentUsername + " has seen your challenge. Waiting on their response...";
			}else{
				return "The challenge with " + opponentUsername + " has been marked as viewed.";
			}
		case "ACCEPTED":
			if(this.challenger){
				return opponentUsername + ' has accepted your challenge. Click <a href="javascript:window.open(\'game.php?gameID='+this.gameID+'\')">here</a> to open the new game.';
			}else{
				return 'You have accepted ' + opponentUsername + '\'s challenge. Click <a href="javascript:window.open(\'game.php?gameID='+this.gameID+'\')">here</a> to open the new game.';
			}
		case "DENIED":
			if(this.challenger){
				return opponentUsername + " has declined your challenge. Try challenging another online player!";
			}else{
				return "You have declined the challenge with " + opponentUsername;
			}
		case "CANCELLED":
			if(this.challenger){
				return "You have cancelled the challenge to " + opponentUsername + ".";
			}else{
				return opponentUsername + " has cancelled the challenge.";
			}
		default:
			alert(this.status + " is not a valid status");
	}
				
}

Challenge.prototype.displayOnlinePlayers = function(){
	ajaxCall("get",
		{
			application: "challenge",
			method: "getOnlinePlayers",
			data: { room: 0 }
		}, function(data){
			data = data["challenge"];

			var $onlinePlayersContainer = $("#onlinePlayers ul");
			$onlinePlayersContainer.html("");
			for(var i = 0; i < data.length; i++){
				if(window.player.id != data[i].id){
					var $li = $('<li id="onlinePlayer'+data[i].id+'">');
					var $a = $("<a>");
					$a.attr("href", "javascript://");
					$a.click(function(){
						var otherID = $(this).parent().attr("id").substring("onlinePlayer".length);
						var otherUsername = $(this).text();
						otherID = parseInt(otherID);
						if(window.chats[otherID] !== undefined){
							// There is already a chat going with this user
							if(window.chats[otherID].challenge === undefined){
								window.chats[otherID].challenge = new Challenge(true, {id:otherID, username:otherUsername}, "PENDING");
							}else{
								window.chats[otherID].challenge.selectNewStatus("PENDING");
							}
						}else{
							// There isn't a chat going with this user - set one up, minimized
							var chatDiv = Chat.prototype.createChatWindow(otherUsername);
							window.chats[otherID] = new Chat(chatDiv, {id:otherID, username:otherUsername});
						
							window.chats[otherID].challenge = new Challenge(true, {id:otherID, username:otherUsername}, "PENDING");
							window.chats[otherID].challenge.selectNewStatus("PENDING");

						}


						//var challenge = new Challenge(window.player.id, otherID, "PENDING");
					});
					$a.text(data[i].username);
					$li.append($a);
					$onlinePlayersContainer.append($li);
				}
			}

		}
	);
}

Challenge.prototype.selectNewStatus = function(newStatus){
	var which = this;
	
	ajaxCall("post",
		{
			application: "challenge",
			method: "updateChallengeStatus",
			data: {
				challengerID: (which.challenger ? window.player.id : which.opponentID),
				challengeeID: (which.challenger ? which.opponentID : window.player.id),
				newStatus: newStatus
			}
		}, function(data){
			if(data["challenge"].success === undefined || data["challenge"].success !== true){
				// Failure
				alert(data["challenge"].error);
			}else{
				which.setStatus(newStatus);
				which.gameID = data["challenge"].gameID;
				window.chats[which.opponentID].updateChallengeMessage();
			}
		}
	);
}



$(document).ready(function(){
	setInterval(function(){Challenge.prototype.displayOnlinePlayers();}, 3000);

	setInterval(function(){

		ajaxCall("get", 
			{
				application: "challenge",
				method: "getChallenges", 
				data: {"playerID" : window.player["id"]}
			}, function(data){
				//Function to handle receiving challenge updates
				data = data["challenge"];
				
				for(var i = 0; i < data.length; ++i){
					var challenger;
					var otherID;
					if(data[i].challengerID == window.player.id){
						challenger = true;
						otherID = data[i].challengeeID;
						otherUsername = data[i].challengeeUsername;
					}else if(data[i].challengeeID == window.player.id){
						challenger = false;
						otherID = data[i].challengerID;
						otherUsername = data[i].challengerUsername;
					}else{
						console.log("user - " + window.player.id);
						console.log("The challenge ajax call returned a challenge that doesn't belong to this user.");
						console.log(data[i]);
					}
					

					if(data[i].status !== "CANCELLED" && data[i].status !== "DENIED"){
						if(window.chats[otherID] !== undefined){
							// There is already a chat going with this user
							if(window.chats[otherID].challenge === undefined){
								window.chats[otherID].challenge = new Challenge((data[i].challengerID == window.player.id), {id:otherID, username:otherUsername}, data[i].status);
							}else{
								window.chats[otherID].challenge.setStatus(data[i].status);
							}
							window.chats[otherID].updateChallengeMessage();
						}else{
							// There isn't a chat going with this user - set one up, minimized
							var chatDiv = Chat.prototype.createChatWindow(otherUsername);
							window.chats[otherID] = new Chat(chatDiv, {id:otherID, username:otherUsername});

							window.chats[otherID].challenge = new Challenge((data[i].challengerID == window.player.id), {id:otherID, username:otherUsername}, data[i].status);
						}
					}

					// When a challenge gets accepted get the new gameID
					if(data[i].status === "ACCEPTED"){
						//alert(data[i].gameID);
						window.chats[otherID].challenge.gameID = data[i].gameID;
					}

					
				}
			}
		);
	}, 2000);
	
});
