<?php

/**
 * @author over_coder <over.coder@yahoo.it>
 * Twitter: @over_coder
 */

	function log_error($error = null) {
		if(!is_null($error)) {
			// Creating and/or writing the log error file
			try {
				$file = fopen("./yt_app_error.log", "a+");
				$result = fwrite($file, date('d/m/Y h:i:s a', time()) . "\n" . $error . "\n\n");
				fclose($file);
			}
			catch(Exception $e) {
				// Null for the moment
				return false;
			}
		}
	}
	
	function error_msg($message = null) {
		if(!is_null($message)) {
			try {
				return '<div id="container">
					<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
					<div id="error">' . $message . '</div>
					</div>';
			}
			catch(Exception $e) {
				$this->log_error($e);
				return false;
			}
		}
	}
	
	function success_msg($message = null) {
		if(!is_null($message)) {
			try {
				return '<div id="container">
					<a class="nulled" href="index.php"><h1>NoDZF <span class="tube_back">Tube</span></h1></a>
					<div id="success">' . $message . '</div>
					</div>';
			}
			catch(Exception $e) {
				$this->log_error($e);
				return false;
			}
		}
	}
	
	function getExtension($filename = null) {
		if(!is_null($filename))
			return substr($filename, strpos($filename,'.'), strlen($filename)-1);
		return false;
	}
?>
