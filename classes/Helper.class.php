<?php
class Helper {
	
	static $cacheDir = '/cache';
	
	static function loadClasses(){
		require_once $GLOBALS['rootDir']."/classes/singleton.class.php";
		foreach(glob($GLOBALS['rootDir']."/classes/*.php") as $file){
			require_once $file;
		}
	}

	
	static function getRoleName($roleID){
		
		$query = $GLOBALS['pdo']->prepare("SELECT name FROM roles WHERE roleID= :role");
		$query->bindValue(':role', $roleID, PDO::PARAM_INT);
		$query->execute();
		
		$data = $query->fetch();
		return $data['name'];
	}
	
	
	static function generatePassword($length) {
	
		$pass = '';
		
		for ($i = 0; $i < $length; $i++) {
	
			$rand = rand(1,3);
		
			switch($rand) {
				case 1: 
					$pass .= chr(rand(48,57)); 
					break;
				case 2: 
					$pass .= chr(rand(65,90)); 
					break;
				default: 
					$pass .= chr(rand(97,122)); 
					break;
			}
		}
		return $pass;
	}
		
	static function toBytes($val) {
		$val = trim($val);
		$last = preg_replace("/[^a-z]/","",strtolower($val));
		//$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
			case 'gb';
				$val *= 1024;
			case 'm':
			case 'mb':
				$val *= 1024;
			case 'k':
			case 'kb':
				$val *= 1024;
		}
	
		return $val;
	}
	
}