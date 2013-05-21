<?php

	require_once("../../frontend/config.php");

	require_once(dirname(__FILE__) . "/../simpletest/autorun.php");
	require_once(dirname(__FILE__) . "/../dataLayer/DataLayer.class.php");
	require_once(dirname(__FILE__) . "/ResetDatabase.php");

	
	class TestDataLayer extends UnitTestCase{
		function start(){
			dropAllTables();
			addTables();
		}

		function testChat(){
			$this->assertTrue(false, "Chats work, so I'll test them later if I'm having issues.");
		}

	}
?>