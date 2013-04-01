<?php

	class DataLayer{
		
		protected $mysqli;

		public __construct($db_host, $db_username, $db_password, $db_database){
			$this->mysqli = new mysqli($db_host, $db_username, $db_password, $db_database);
		
			if($this->mysqli->connect_errno > 0){
				throw new RuntimeException("Could not connect to database");
			}

		}

		public __destruct(){
			$this->mysqli->close();
		}

	}


?>
