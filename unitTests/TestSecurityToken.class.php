<?php

	require_once("../backend/SecurityToken.class.php");
	require_once(dirname(__FILE__) ."/simpletest/autorun.php");

	class TestSecurityToken extends UnitTestCase{

		function testToken(){
				echo $_SERVER["REMOTE_ADDR"];
			echo "testToken()<br />";

			$token = SecurityToken::create(345);
			echo "token - " . $token . "<br />";
			echo "ip - " . $_SERVER['REMOTE_ADDR'] . "<br />";
			echo "timestamp - " . time() . "<br />";

			echo "<br />run<br /><br />";

			try{
				$array = SecurityToken::extract($token);
				print_r($array);
			}catch(Exception $e){
				echo "invalid token<br />";
			}

		}

	}


?>
