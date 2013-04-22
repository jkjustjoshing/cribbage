function ajaxCall(getOrPost,data,callback){
	$.ajax({
 		type: getOrPost,
 		async: true, 
  		cache:false,
  		url: "mid.php",
  		data: data,  
  		dataType: "json",
  		success: callback
	});
}

