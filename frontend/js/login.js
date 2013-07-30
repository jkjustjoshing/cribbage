/**
 * Toggles if the user is in a sign up or sign in mode.
 * If this is called while the page is open it will clear any
 * error message. If this is being called onload it will not 
 * hide any error messages.
 * @param  {boolean} startup Whether or not this is being called onload
 */
function toggleSignup(startup){
	$hidden = $('form:hidden');
	$visible = $('form:visible');
	$hidden.show();
	$visible.hide();
	
	if(typeof startup === 'undefined') $(".alert").hide();
	
}

/**
 * Checks an email according to either a global regex or a local one
 * if the global one isn't found. Colors the input box light red
 * if the email is invalid.
 * @param  {DOMNode} which The DOM input element holding the email
 */
function checkEmail(which){
	if(emailRegex === undefined){
		emailRegex = /^[a-zA-Z0-9_+.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,8}$/;
		console.log("emailRegex is not defined. Using default email regex.");
	}
	var valid = emailRegex.exec($(which).val());
	var color;
	if(!valid){
		color = "#f77";
	}else{
		color = "white";
	}
	
	if(this.keyup_email === undefined) $(which).on("keyup", function(){checkEmail(this);});
	this.keyup_email = true;
	
	$(which).css("background", color);
}

/**
 * Checks a password according to either a global regex or a local one
 * if the global one isn't found. Colors the input box light red
 * if the password is invalid.
 * @param  {DOMNode} which The DOM input element holding the password
 */	
function checkPassword(which){
	if(minimumPasswordLengthRegex === undefined){
		minimumPasswordLengthRegex = /^[a-zA-Z0-9_]{8,}$/;
		console.log("minimumPasswordLengthRegex is not defined, so the default password minimum of 8 is being used.")
	}
	var valid = minimumPasswordLengthRegex.exec($(which).val());
	var color;
	if(!valid){
		color = "#f77";
	}else{
		color = "white";
	}
	
	if(this.keyup_password === undefined) $(which).on("keyup", function(){checkPassword(this);});
	this.keyup_password = true;
	
	$(which).css("background", color);
}

/**
 * Checks a username according to either a global regex or a local one
 * if the global one isn't found. Colors the input box light red
 * if the username is invalid.
 * @param  {DOMNode} which The DOM input element holding the username
 */
function checkUsername(which){
	if(usernameWhitelistRegex === undefined){
		usernameWhitelistRegex = /^[a-zA-Z0-9_]+$/;
		console.log("usernameWhitelistRegex is not defined. Using default username whitelist regex.");
	}
	var valid = usernameWhitelistRegex.exec($(which).val());
	var color;
	if(!valid){
		color = "#f77";
	}else{
		color = "white";
	}
	
	if(this.keyup_username === undefined) $(which).on("keyup", function(){checkUsername(this);});
	this.keyup_username = true;
	
	$(which).css("background", color);
}
