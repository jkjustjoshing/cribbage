function ajaxCall(getOrPost,data,callback){
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

