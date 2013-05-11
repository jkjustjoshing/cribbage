function ajaxCall(getOrPost,data,callback){
	if(window.gameID !== undefined){
		data.heartbeatRoom = window.gameID
	}else{
		data.heartbeatRoom = 0; // For the lobby
	}

	$.ajax({
 		type: getOrPost,
 		async: true, 
  		cache:false,
  		url: "ajax.php",
  		data: data,  
  		dataType: "json",
  		success: callback
	});
}

