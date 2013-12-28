<?php

// PHP configuration file

class Config
{
	// Database data
	
	private $host = 'localhost';
	private $db_username = 'username';
	private $db_password = 'password';
	private $database = 'database';
	
	// Admin data
	
	private $username = 'username';
	private $password = 'password';
	
	//  --------------------------------------------------------------------------------------------------
	
	// DB host
	
	function setDbHost($host)
	{
		try {
			$this->$host = htmlspecialchars($host);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getDbHost()
	{
		return htmlspecialchars($this->host);
	}
	
	// DB username
	
	function setDbUsername($db_username)
	{
		try {
			$this->$db_username = htmlspecialchars($db_username);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getDbUsername()
	{
		return htmlspecialchars($this->username);
	}
	
	// DB password
	
	function setDbPassword($db_password)
	{
		try {
			$this->$db_password = htmlspecialchars($db_password);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getDbPassword()
	{
		return htmlspecialchars($this->password);
	}
	
	// DB database
	
	function setDbDatabase($database)
	{
		try {
			$this->$database = htmlspecialchars($database);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getDbDatabase()
	{
		return htmlspecialchars($this->database);
	}
	
	// ---------------------------------------------------
	
	function setUsername($username) {
		try {
			$this->username = htmlspecialchars($username);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getUsername() {
		return htmlspecialchars($this->username);
	}
	
	function setPassword($password) {
		try {
			$this->password = htmlspecialchars($password);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getPassword() {
		return htmlspecialchars($this->password);
	}
}

?>
