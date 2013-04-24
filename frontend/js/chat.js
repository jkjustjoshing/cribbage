function Chat(container){
	this.container = $(container);
	
	
	// Create elements for chat
	var ele = this.container.get();	
	var conversation = document.createElement("div");
	conversation.setAttribute("class", "conversation");
	var form = document.createElement("form");
	form.setAttribute("class", "send");
	form.setAttribute("onsubmit", function(){
		return sendChat(this);
	});
	var input = document.createElement("input");
	input.setAttribute("type", "text");
	input.setAttribute("name", "text");
	form.appendChild(input);
	ele.appendChild(conversation);
	ele.appendChild(form);
	
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
	if(hoursSince == 1)
		return '1 hour ogo';
	if(hoursSince < 12)
		return hoursSince + ' hours ago';
	
	//print more than that
	var jsDate = new Date(postTime*1000);
	var daysSince = Math.round(hoursSince/24);
	if(daysSince == 1)
		var str = 'Yesterday at ';
	else if(daysSince == 2)
		var str = '2 days ago at ';
	else{
		var month = ['Jan','Feb','March','April','May','June','July','Aug','Sept','Oct','Nov','Dec'];
		var str = month[jsDate.getMonth()] + ' ' + jsDate.getDate();
		str += ', ' + jsDate.getFullYear() + ' at ';
	}
	var hours = jsDate.getHours()%12;
	if(hours == 0)
		hours = 12;
	str += hours + ':'+ ((jsDate.getMinutes() < 10) ? ('0'+jsDate.getMinutes()) : jsDate.getMinutes()) + ' ';
	str += (jsDate.getHours() < 12)? 'am' : 'pm' ;
	
	return str;
}

function createChatItem(name, time, message){
	var container = document.createElement("div");
	container.setAttribute("class", "chatItem");
	
	var nameEle = document.createElement("div");
	nameEle.setAttribute("class", "name");
	$(nameEle).text(name)
	
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

var chatID;
var pause = false;
$(document).ready(function(){

	
	setInterval(function(){
		
		if(pause){
			pause = false;
			return;
		}
		
		data = {"opponentID": window.opponentID["id"], "playerID":window.playerID["id"]};
		if(chatID !== undefined){
			data.lastSeenID = chatID;
		}
	
		ajaxCall("get", 
			{
				application: "chat",
				method: "getChat", 
				data: data
			}, receiveChats
		);
	}, 2000);
	
});

function sendChat(which){
	
	pause = true;
	
	data = {"opponentID": window.opponentID["id"], "playerID":window.playerID["id"], "content":$(which).children("input").val()};
	if(chatID !== undefined){
		data.lastSeenID = chatID;
	}
		
	ajaxCall("get", 
		{
			application: "chat",
			method: "postChat", 
			data: data
		}, receiveChats
	);

	return false;
}

function receiveChats(data){
	window.currentTime = data["info"]["time"];
	data = data["chat"];
	var $chat = $($(".conversation"));
	alert(data.length);
	for(var i = data.length-1; i >= 0; --i){
		var chatItem = data[i];
		alert(chatItem);
		$chat.append(createChatItem(chatItem["posterID"], chatItem["timestamp"], chatItem["content"]));
		chatID = chatItem["id"];
	}
	
	// Scroll the chat window to the bottom
	if(data.length !== 0){
		$chat.animate({"scrollTop": $chat[0].scrollHeight}, "slow");
	}
}