<?php

/**
 * @author over_coder <over.coder@yahoo.it>
 * Twitter: @over_coder
 */
	require_once './config.php';
	
	class Database
	{
		
		public $conn;
		
		function __construct()
		{
			$config = new Config;
			$this->conn = mysqli_connect($config->getDbHost(), $config->getDbUsername(), $config->getDbPassword(), $config->getDbDatabase()) or die (log_error(mysqli_error()));
		}
		
		function getConn() {
			return $this->conn;
		}
		
		function query($query, $sanitize = true)
		{
			if($sanitize)
				$query = htmlspecialchars($query);
			
			try
			{
				$result = mysqli_query($this->getConn(), $query) or die (log_error($query));
				return $result;
			}
			catch(Exception $e)
			{
				return false;
			}
			return false;
		}
		
		function close()
		{
			try
			{
				mysqli_close($this->getConn());
			}
			catch(Exception $e)
			{
				return false;
			}
			return true;
		}
		
		function setConn(&$conn) {
			try {
				$this->conn = &$conn;
			}
			catch(Exception $e) {
				return false;
			}
		}
		
		function real_escape_string($arg) {
			return mysqli_real_escape_string($this->getConn(), $arg);
		}
		
		function fetch_array($arg) {
			return mysqli_fetch_array($this->getConn(), $arg);
		}
		
		// scott_carney solutions
		
		function result_array($result, $key_column = null)
		{ 
			for ($array = array(); $row = mysqli_fetch_assoc($result); isset($row[$key_column]) ? $array[$row[$key_column]] = $row : $array[] = $row);
			return $array;
		}
		
		function result_array_values($result)
		{ 
			for ($array = array(); $row = mysqli_fetch_row($result); isset($row[1]) ? $array[$row[1]] = $row[0] : $array[] = $row[0]);
			return $array;
		}
	}
	
?>