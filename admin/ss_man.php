<?php

/**
 * @author over_coder <over.coder@yahoo.it>
 * Twitter: @over_coder
 */

session_start();

class SessionManager {
	
	public $logged = false;
	
	public function __construct() {
		
		if(isset($_SESSION['logged'])) {
			$logged = (bool)$_SESSION['logged'];
			
			if($logged) {
				$this->setLogged(true);
			}
			else {
				$this->setLogged(false);
			}
		}
		else {
			$this->setLogged(false);
		}
	}

	
	public function setLogged($status = null) {
		if(!is_null($status)) {
			$this->logged = (bool)$status;
			$_SESSION['logged'] = (bool)$status;
		}
		else
			return false;
	}
	
	public function isLogged() {
		return $this->logged;
	}
	
	public function close() {
		session_destroy();
	}

}

?>