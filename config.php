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
	
	// Other
	
	private $max_storage_days = 3;
	
	// OAuth 2.0 Keys
	
	private $OAUTH2_CLIENT_ID = 'OAUTH2_CLIENT_ID';
	private $OAUTH2_CLIENT_SECRET = 'OAUTH2_CLIENT_SECRET';
	
	//  --------------------------------------------------------------------------------------------------
	
	// DB host
	
	function setDbHost($host)
	{
		try {
			$this->host = htmlspecialchars($host);
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
			$this->db_username = htmlspecialchars($db_username);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getDbUsername()
	{
		return htmlspecialchars($this->db_username);
	}
	
	// DB password
	
	function setDbPassword($db_password)
	{
		try {
			$this->db_password = htmlspecialchars($db_password);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getDbPassword()
	{
		return htmlspecialchars($this->db_password);
	}
	
	// DB database
	
	function setDbDatabase($database)
	{
		try {
			$this->database = htmlspecialchars($database);
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
	
	//  --------------------------------------------------------------------------------------------------
	
	function setMaxStorageDays($max_storage_days) {
		try {
			$this->max_storage_days = htmlspecialchars($max_storage_days);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getMaxStorageDays() {
		return htmlspecialchars($this->max_storage_days);
	}
	
	//  --------------------------------------------------------------------------------------------------
	
	function setOAUTH2_CLIENT_ID($OAUTH2_CLIENT_ID) {
		try {
			$this->OAUTH2_CLIENT_ID = htmlspecialchars($OAUTH2_CLIENT_ID);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getOAUTH2_CLIENT_ID() {
		return htmlspecialchars($this->OAUTH2_CLIENT_ID);
	}
	
	function setOAUTH2_CLIENT_SECRET($OAUTH2_CLIENT_SECRET) {
		try {
			$this->OAUTH2_CLIENT_SECRET = htmlspecialchars($OAUTH2_CLIENT_SECRET);
		}
		catch(Exception $e) {
			return false;
		}
		return true;
	}
	
	function getOAUTH2_CLIENT_SECRET() {
		return htmlspecialchars($this->OAUTH2_CLIENT_SECRET);
	}
}

?>
