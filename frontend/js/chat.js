
function createChatItem(name, time, message){
	var container = document.createElement("div");
	container.setAttribute("class", "chatItem");
	
	var nameEle = document.createElement("div");
	nameEle.setAttribute("class", "name");
	$(nameEle).text(name)
	
	var timeEle = document.createElement("div");
	timeEle.setAttribute("class", "time");
	$(timeEle).text(time)
	
	var messageEle = document.createElement("div");
	messageEle.setAttribute("class", "message");
	$(messageEle).text(message);
	
	container.appendChild(nameEle);
	container.appendChild(timeEle);
	container.appendChild(messageEle);
	
	return container;
}

var chatTimestamp;

$(document).ready(function(){

	
	setInterval(function(){
		
		data = {"opponentID": window.opponentID, "playerID":window.playerID};
		if(chatTimestamp !== undefined){
			data.lastSeenTimestamp = chatTimestamp;
		}
	
		ajaxCall("get", 
			{
				application: "chat",
				method: "getChat", 
				data: data
			}, function(data){
				var $chat = $(".conversation");
				for(var i = 0; i < data.length; ++i){
					var chatItem = data[i];
					$chat.append(createChatItem(chatItem["posterID"], chatItem["timestamp"], chatItem["content"]));
					chatTimestamp = chatItem["timestamp"];
				}
				
				// Scroll the chat window to the bottom
				$chat.animate({"scrollTop": $chat[0].scrollHeight}, "slow");

				
			}
		);
	}, 1000);
	
});