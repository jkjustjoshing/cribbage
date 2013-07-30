function Chat(container, opponent){
	this.opponentID = (opponent.id === undefined ? opponent : opponent.id);
	this.opponentUsername = (opponent.id === undefined ? opponent : opponent.username);
	
	var which = this;

	if(container === null){
		this.$container = this.createChatWindow(opponent.username, false);
	}else{
		this.$container = $(container);
	}

	var which = this;
	this.$container.find(".close").on("click", function(){
		which.$container.css("display", "none");
	});

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

Chat.prototype.show = function(){
	this.$container.css("display", "block");
}

Chat.prototype.hide = function(){
	this.$container.css("display", "none");
}

Chat.prototype.createChatWindow = function(name, shouldINotDisplayTheEle){
	var div = document.createElement("div");
	div.setAttribute("class", "chat");
//	div.style.left = (window.chats.size()*200 + 5) + "px";

	var nameEle = document.createElement("div");
	nameEle.setAttribute("class", "name");
	nameEle.appendChild(document.createTextNode(name));
	div.appendChild(nameEle);

	var close = document.createElement("div");
	close.setAttribute("class", "close");
	nameEle.appendChild(close);


		// div.innerHTML = '<div class="name">'+name+'<div class="close"></div></div>' +
		// 			'<div class="challenge"></div>' +
		// 			'<div class="conversation">'+
		// 			'</div>' +
		// 			'<form class="send" action="" method="post">' +
		// 				'<input type="text" name="text" autocomplete="off" />' +
		// 			'</form>';

	var challenge = document.createElement("div");
	challenge.setAttribute("class", "challenge");
	div.appendChild(challenge);

	var conversation = document.createElement("div");
	conversation.setAttribute("class", "conversation");
	div.appendChild(conversation);

	var form = document.createElement("form");
	form.setAttribute("class", "send");
	form.setAttribute("action", "");
	form.setAttribute("method", "post");
	div.appendChild(form);

	var input = document.createElement("input");
	input.setAttribute("type", "text");
	input.setAttribute("name", "text");
	input.setAttribute("autocomplete", "off");
	form.appendChild(input);

	if(shouldINotDisplayTheEle !== true){
		document.getElementById("chatContainer").appendChild(div);
	}
	return div;
}

Chat.prototype.createChatItem = function(id, username, time, message){
	var container = document.createElement("div");
	$(container).attr("class", "chatItem chat"+id);
	
	var nameEle = document.createElement("div");
	nameEle.setAttribute("class", "name");
	$(nameEle).text(username + ": ");

	var timeEle = document.createElement("div");
	timeEle.setAttribute("class", "time");
	$(timeEle).text(getTimeString(time))
	
	var messageEle = document.createElement("div");
	messageEle.setAttribute("class", "message");
	$(messageEle).text(message);
	
	container.appendChild(nameEle);
	container.appendChild(messageEle);
	container.appendChild(timeEle);
	
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
	//var daysSince = Math.round(hoursSince/24);
	var str;
	var month = ['Jan','Feb','March','April','May','June','July','Aug','Sept','Oct','Nov','Dec'];
	str = month[jsDate.getMonth()] + ' ' + jsDate.getDate();
	str += ', ' + jsDate.getFullYear() + ', ';
	var hours = jsDate.getHours()%12;
	if(hours === 0){
		hours = 12;
	}
	str += hours + ':'+ ((jsDate.getMinutes() < 10) ? ('0'+jsDate.getMinutes()) : jsDate.getMinutes()) + ' ';
	str += (jsDate.getHours() < 12)? 'am' : 'pm' ;
	
	return str;
}