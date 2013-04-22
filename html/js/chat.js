
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

$(document).ready(function(){
	$chat = $(".conversation");

	for(var i = 0; i < 30; ++i){
		$chat.append(createChatItem("josh", "yesterday", "well hello there!"));
		$chat.append(createChatItem("kristen", "yesterday", "well hello there!"));
		$chat.append(createChatItem("josh", "today", "well hello there!"));
		$chat.append(createChatItem("kristen", "today", "hi!"));
		$chat.append(createChatItem("josh", "yesterday", "well hello there!"));
	}
	
});