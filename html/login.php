<?php

	require_once(dirname(__FILE__) . "/../../../../SecurityToken.class.php");
	require_once(dirname(__FILE__) . "/../businessLayer/Player.class.php");
		
	if(SecurityToken::isTokenSet()){
		$userID = SecurityToken::extract();
		if($userID === false){
			// Token was set, but something was wrong. Logged out.
			// Show login screen with error message
			$errorMessage = "There was an error. Please log in again.";
		}else{
			// Already logged in
			header("Location: home.php");
		}
	}else if(isset($_POST["signin"])){
		// Logging in - try logging in
		
		$login = Player::login($_POST["username"], $_POST["password"]);
		
		if($login === false){
			// Bad password - not logged in
			// Show login screen with error message
			$errorMessage = "Your username or password was incorrect. Please log in again.";
		}else{
			//Successful login!
			SecurityToken::create($login->id);
			header("Location: home.php");
		}	
	}else if(isset($_POST["signup"])){
		// Signing up - add account
		$errorMessage = "Adding an account is not supported just yet.";
		
	}
	
	$numChars = 8;

?><!DOCTYPE html>
<html>
  <head>
    <title>Cribbage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Bootstrap -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
 	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
 	<script type="text/javascript">
 	//<![CDATA[
		function toggleSignup(startup){
 			$hidden = $('form:hidden');
 			$visible = $('form:visible');
 			$hidden.show();
 			$visible.hide();
 			
 			if(typeof startup === 'undefined') $(".alert").hide();
 			
 		}
 		
 		function checkEmail(which){
			var valid = /^[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,8}$/.exec($(which).val());
			var color;
			if(!valid){
				color = "#f77";
			}else{
				color = "white";
			}
			
			if(this.keypress === undefined) $(which).on("keyup", function(){checkEmail(this);});
			this.keypress = true;
			
			$(which).css("background", color);
		}
 		
 		function checkPassword(which){
			var valid = /^[a-zA-Z0-9_]{<?php echo $numChars; ?>,}$/.exec($(which).val());
			var color;
			if(!valid){
				color = "#f77";
			}else{
				color = "white";
			}
			
			if(this.keypress === undefined) $(which).on("keypress", function(){checkPassword(this);});
			this.keypress = true;
			
			$(which).css("background", color);
		}
 		
 		$(document).ready(function(){
 			$("form.signup #email").on("blur", function(){checkEmail(this);});
 			$("form.signup #password").on("blur", function(){checkPassword(this)});
 			
 			<?php
 				if(isset($_POST["signup"])) echo 'toggleSignup(true);';
 			?>
 		});

	//]]>
 	</script>
 	<style type="text/css">
    	.container {
    		padding:20px;
    		margin: 0 auto 20px;
    		width:400px;
    		border: 1px solid #BBB;
    		border-radius:6px;
    	}
    
    	form.signup {
    		display:none;
    	}
    
    	form a.alternateLink {
    		float:right;
    		margin: 10px 0px;
    	}
    	
    	form label div {
    		margin:5px 0px;
    		float:left;
    		width:20%;
    	}
    	
    	form label .input {
    		width:80%;
    		float:left;
    	}

    </style>
  </head>
  <body>
  <div class="container">
    <form class="signin" method="post" action="">
    	<h2> sign in</h2>

<?php
    	if(isset($errorMessage)){
    		echo '<div class="alert">';
    		echo $errorMessage;
    		echo '</div>';
		}
?>

    	<label for="username"><div>username</div><input id="username" type="text" class="input-block-level input" name="username" /></label>
    	<label for="password"><div>password</div><input id="password" type="password" class="input-block-level input" name="password" /></label>
    	<button class="btn btn-large btn-primary" type="submit">Sign in</button>
    	<a class="alternateLink" href="javascript://" onclick="toggleSignup()">Sign up</a>
    	<input type="hidden" name="signin" />
    </form>
    <form class="signup" method="post" action="">
    	<h2>sign up</h2>
<?php
    	if(isset($errorMessage)){
    		echo '<div class="alert">';
    		echo $errorMessage;
    		echo '</div>';
		}
?>
    	<label for="username"><div>username</div><input id="username" name="username" type="text" class="input-block-level input" placeholder="only letters, numbers, and underscores" /></label>
    	<label for="password"><div>password</div><input id="password" name="password" type="password" class="input-block-level input" placeholder="at least <?php echo $numChars; ?> characters" /></label>	
    	<label for="email"><div>email</div><input id="email" name="email" type="text" class="input-block-level input" placeholder="must be valid" /></label>
    	<button class="btn btn-large btn-primary" type="submit">Create account</button>
    	<a class="alternateLink" href="javascript://" onclick="toggleSignup()">I have an account</a>
    	<input type="hidden" name="signup" />
    </form>
    </div>
  </body>
</html>
