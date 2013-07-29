function ChatHandler(playerID, opponentID){
	this.playerID = (playerID.id === undefined ? playerID : playerID.id);
	this.opponentID = (opponentID.id === undefined ? opponentID : opponentID.id);

	var which = this;


	setInterval(function(){	
		data = {"opponentID": which.opponentID, "playerID":window.player["id"]};
		if(which.lastSeenID !== undefined){
			data.lastSeenID = which.lastSeenID;
		}	
		ajaxCall("get", 
			{
				application: "chat",
				method: "getChat", 
				data: data
			}, function(data){which.receiveChats(data);}
		);
	}, 2000);
		
}

ChatHandler.prototype.listen = function(){
	var which = this;
	this.interval = setInterval(function(){
		data = {"opponentID": which.opponentID, "playerID": which.playerID};
		if(which.lastSeenID !== undefined){
			data.lastSeenID = which.lastSeenID;
		}	
		ajaxCall("get", 
			{
				application: "chat",
				method: "getChat", 
				data: data
			}, function(data){
				//	which.receiveChats(data);
				var returnVal = true;

				window.currentTime = data["info"]["time"];
				data = data["chat"];
				
				if(data["error"] != undefined){
					// For chat errors just redirect to the login page
					// the user is likely logged out
					window.location = "login.php";
					returnVal = false;
				}
				
				var $chat = this.$container.children(".conversation");
				
				for(var i = data.length-1; i >= 0; --i){
					var chatItem = data[i];
					$chat.append(this.createChatItem(chatItem["posterID"], chatItem["posterUsername"], chatItem["timestamp"], chatItem["content"]));
					this.lastSeenID = chatItem["id"];
				}
				
				// Scroll the chat window to the bottom
				if(data.length !== 0){
					$chat.animate({"scrollTop": $chat[0].scrollHeight}, "slow");
				}
				
				return returnVal;
			}
		);
	}, 2000);
}


/**
 * Chat handler
 */

Chat.prototype.sendChat = function(whichFormEle){
	data = {"opponentID": this.opponentID, "playerID":window.player["id"], "content":$(whichFormEle).children("input").val()};
	if(this.lastSeenID !== undefined){
		data.lastSeenID = this.lastSeenID;
	}
	
	var which = this;

	ajaxCall("post",
		{
			application: "chat",
			method: "postChat", 
			data: data
		}, function(data){ if(which.receiveChats(data)) $(whichFormEle).children("input").val(""); }
	);

	return false;
}

Chat.prototype.receiveChats = function(data){
	var returnVal = true;

	window.currentTime = data["info"]["time"];
	data = data["chat"];
	
	if(data["error"] != undefined){
		// For chat errors just redirect to the login page
		// the user is likely logged out
		window.location = "login.php";
		returnVal = false;
	}
	
	var $chat = this.$container.children(".conversation");
	
	for(var i = data.length-1; i >= 0; --i){
		var chatItem = data[i];
		$chat.append(this.createChatItem(chatItem["posterID"], chatItem["posterUsername"], chatItem["timestamp"], chatItem["content"]));
		this.lastSeenID = chatItem["id"];
	}
	
	// Scroll the chat window to the bottom
	if(data.length !== 0){
		$chat.animate({"scrollTop": $chat[0].scrollHeight}, "slow");
	}
	
	return returnVal;
}

Chat.prototype.setChallenge = function(challenge){
	if(this.opponentID == 0){
		alert("Can't challenge the lobby.");
	}else{
		this.challenge = challenge;
	}
}

Chat.prototype.updateChallengeMessage = function(){
	this.$container.children(".challenge").html(this.challenge.getMessage());
}


function getTimeString(postTime){
	var jsDate = new Date(postTime*1000);
	var daysSince = Math.round(hoursSince/24);
	var str;
	var month = ['Jan','Feb','March','April','May','June','July','Aug','Sept','Oct','Nov','Dec'];
	str = month[jsDate.getMonth()] + ' ' + jsDate.getDate();
	str += ', ' + jsDate.getFullYear() + ' at ';
	var hours = jsDate.getHours()%12;
	if(hours === 0){
		hours = 12;
	}
	str += hours + ':'+ ((jsDate.getMinutes() < 10) ? ('0'+jsDate.getMinutes()) : jsDate.getMinutes()) + ' ';
	str += (jsDate.getHours() < 12)? 'am' : 'pm' ;
	
	return str;
}