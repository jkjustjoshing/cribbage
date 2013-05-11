function Chat(container, opponentID){
	this.$container = $(container);
	this.opponentID = opponentID;
	
	var which = this;

	this.$container.children("form").on("submit", function(){
		return which.sendChat(this);
	});
	
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

Chat.prototype.createChatWindow = function(name, shouldINotDisplayTheEle){
	var div = document.createElement("div");
	div.setAttribute("class", "chat");
	div.style.left = (window.chats.size()*200 + 5) + "px";
	div.innerHTML = '<div class="name">'+name+'</div>' +
					'<div class="challenge"></div>' +
					'<div class="conversation">'+
					'</div>' +
					'<form class="send" action="" method="post">' +
						'<input type="text" name="text" autocomplete="off" />' +
					'</form>';
	if(shouldINotDisplayTheEle !== true){
		document.getElementById("lobbyContainer").appendChild(div);
	}
	return div;
}

Chat.prototype.createChatItem = function(id, username, time, message){
	var container = document.createElement("div");
	$(container).attr("class", "chatItem chat"+id);
	
	var nameEle = document.createElement("div");
	nameEle.setAttribute("class", "name");
	$(nameEle).text(username);

	var timeEle = document.createElement("div");
	timeEle.setAttribute("class", "time");
	$(timeEle).text(getTimeString(time))
	
	var messageEle = document.createElement("div");
	messageEle.setAttribute("class", "message");
	$(messageEle).text(message);
	
	container.appendChild(nameEle);
	container.appendChild(timeEle);
	container.appendChild(messageEle);
	
	return container;
}

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
		alert(data["error"]);
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

	var secondsSince = window.currentTime - postTime;

	//print seconds ago
	if(secondsSince < 5)
		return 'moments ago';
	if(secondsSince < 60)
		return secondsSince + ' seconds ago';
	
	//print minutes ago
	var minutesSince = Math.round(secondsSince/60);
	if(minutesSince == 1)
		return '1 minute ago';
	if(minutesSince < 60)
		return minutesSince + ' minutes ago';
	
	//print hours ago
	var hoursSince = Math.round(minutesSince/60);
	if(hoursSince === 1){
		return '1 hour ogo';
	}
	if(hoursSince < 12){
		return hoursSince + ' hours ago';
	}

	//print more than that
	var jsDate = new Date(postTime*1000);
	var daysSince = Math.round(hoursSince/24);
	var str;
	if(daysSince === 1)
		str = 'Yesterday at ';
	else if(daysSince === 2)
		str = '2 days ago at ';
	else{
		var month = ['Jan','Feb','March','April','May','June','July','Aug','Sept','Oct','Nov','Dec'];
		str = month[jsDate.getMonth()] + ' ' + jsDate.getDate();
		str += ', ' + jsDate.getFullYear() + ' at ';
	}
	var hours = jsDate.getHours()%12;
	if(hours === 0){
		hours = 12;
	}
	str += hours + ':'+ ((jsDate.getMinutes() < 10) ? ('0'+jsDate.getMinutes()) : jsDate.getMinutes()) + ' ';
	str += (jsDate.getHours() < 12)? 'am' : 'pm' ;
	
	return str;
}