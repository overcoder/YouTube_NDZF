<?php

// PHP configuration file

class Config
{
	// Database data
	
	public $host = 'localhost';
	public $username = 'username';
	public $password = 'password';
	public $database = 'database';
	
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
	
	function setDbUsername($username)
	{
		try {
			$this->$username = htmlspecialchars($username);
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
	
	function setDbPassword($password)
	{
		try {
			$this->$password = htmlspecialchars($password);
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
}

?>
