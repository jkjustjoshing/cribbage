<?php
	require_once('config.php');
	
	require_once(BACKEND_DIRECTORY . "/SecurityToken.class.php");
	require_once(BACKEND_DIRECTORY . "/businessLayer/Player.class.php");
		
	
	if(SecurityToken::isTokenSet()){
		$userID = SecurityToken::extract();

		if($userID === false){
			// Token was set, but something was wrong. Logged out.
			// Show login screen with error message
			$errorMessage = "There was an error. Please log in again.";
		}else{
			// Already logged in
			header("Location: lobby.php");
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
			header("Location: lobby.php");
		}	
	}else if(isset($_POST["signup"])){
		// Signing up - add account
		
		$createAccount = Player::createAccount($_POST["username"], $_POST["password"], $_POST["email"]);
		if($createAccount === true){
			$errorMessage = "Account created. Please log on with your new username and password.";
		}else{
			$errorMessage = $createAccount;
		}
		
	}
	
?><!DOCTYPE html>
<html>
  <head>
    <title>Cribbage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Bootstrap -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="css/main.css" rel="stylesheet" media="screen">
    <script type="text/javascript">
    	var minimumPasswordLengthRegex = /^[a-zA-Z0-9_]{<?php echo Player::MIN_PASSWORD_CHARS; ?>,}$/;
    	var emailRegex = <?php echo Player::EMAIL_REGEX; ?>;
    	var usernameWhitelistRegex = <?php echo Player::USERNAME_WHITELIST; ?>;
    </script>
 	<script type="text/javascript" src="js/jquery.min.js"></script>
 	<script type="text/javascript" src="js/login.js"></script>

  </head>
  <body id="login">
  <h1>Cribbage</h1>
  <div class="container">
    <form class="signin" method="post" action="">
    	<h1> sign in</h1>

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
    	<h1>sign up</h1>
<?php
    	if(isset($errorMessage)){
    		echo '<div class="alert">';
    		echo $errorMessage;
    		echo '</div>';
		}
?>
    	<label for="username"><div>username</div><input id="username" name="username" type="text" class="input-block-level input" placeholder="only letters, numbers, and underscores" /></label>
    	<label for="password"><div>password</div><input id="password" name="password" type="password" class="input-block-level input" placeholder="at least <?php echo Player::MIN_PASSWORD_CHARS; ?> characters" /></label>	
    	<label for="email"><div>email</div><input id="email" name="email" type="text" class="input-block-level input" placeholder="must be valid" /></label>
    	<button class="btn btn-large btn-primary" type="submit">Create account</button>
    	<a class="alternateLink" href="javascript://" onclick="toggleSignup()">I have an account</a>
    	<input type="hidden" name="signup" />
    </form>

    <div id="footer">A game by Josh Kramer</div>

    </div>

    <script type="text/javascript">
	    <?php
			if(isset($_POST["signup"]) && $createAccount !== true) echo 'toggleSignup(true);';
		?>
		$("form.signup #email").on("blur", function(){checkEmail(this);});
		$("form.signup #password").on("blur", function(){checkPassword(this)});
		$("form.signup #username").on("blur", function(){checkUsername(this)});
    </script>
  </body>
</html>
