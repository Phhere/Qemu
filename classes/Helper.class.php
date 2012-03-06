<?php
class Helper {

	const RAM_LIMIT = 0;
	const USER_LIMIT = -1;
	const RUN = 1;
	
	static $cacheDir = '/cache';
	
	static function loadClasses(){
		foreach(glob($GLOBALS['rootDir']."/classes/*.php") as $file){
			require_once $file;
		}
	}

	static function isOwner($vmID){
		if(isset($_SESSION['user'])){
			$query = $GLOBALS['pdo']->prepare("SELECT owner FROM vm WHERE vmID = :vmID AND owner = :owner");
			$query->bindValue(':vmID', $vmID, PDO::PARAM_INT);
			$query->bindValue(':owner', $_SESSION['user']->id, PDO::PARAM_INT);
			$query->execute();
			
			if($query->rowCount()>0){
				return true;
			}
			elseif($_SESSION['user']->role['vm_create'] == 1){
				return true;
			}
			else{
				return false;
			}
		}
		return false;
	}

	static function hasRessources($ram_needed) {
		
		$query = $GLOBALS['pdo']->prepare("SELECT count(vmId) AS `running` FROM vm WHERE status = :status");
		$query->bindValue(':status',QemuMonitor::RUNNING , PDO::PARAM_INT);
		$query->execute();
		
		$data = $query->fetch();
		
		$run = true;
		
		if($data['running'] > $GLOBALS['config']['max_running_vms']){
			$run = self::RAM_LIMIT;
		}
		
		if($_SESSION['user']->role['vm_create'] == 0){
			$query2 = $GLOBALS['pdo']->prepare("SELECT count(vmId) AS `running` FROM vm WHERE status = :status AND owner= :owner");
			$query2->bindValue(':status',QemuMonitor::RUNNING , PDO::PARAM_INT);
			$query2->bindValue(':owner',$_SESSION['user']->id, PDO::PARAM_INT);
			$query2->execute();
			
			$data = $query2->fetch();
			if($data['running'] > $GLOBALS['config']['running_vms']){
				$run = self::USER_LIMIT;
			}
		}
		
		/*$ram = self::getQemuRamTemp();
		if($ram != false && $ram + $ram_needed  > $GLOBALS['config']['max_ram']*1024*1024*1024){
			$run = false;
		}*/
		
		return $run;
	}
	
	static function getUserName($userID){
		$query = $GLOBALS['pdo']->prepare("SELECT email FROM users WHERE userID= :userID");
		$query->bindValue(':userID', $userID, PDO::PARAM_INT);
		$query->execute();
		
		$data = $query->fetch();
		return $data['email'];
	}
	
	static function getRoleName($roleID){
		
		$query = $GLOBALS['pdo']->prepare("SELECT name FROM roles WHERE roleID= :role");
		$query->bindValue(':role', $roleID, PDO::PARAM_INT);
		$query->execute();
		
		$data = $query->fetch();
		return $data['name'];
	}
	static function getRamSettings(){
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return false;
		}
		else{
			$out = exec("free | grep Mem:");
			$values =array_values(array_filter(array_map("trim", explode(" ",$out))));
			$free = $values[3]*1000;
			$used = $values[2]*1000;
			$all = $values[1]*1000;
			return array('all'=>$all,'free'=>$free,'used'=>$used);
		}
	}
	static function getCPUusage(){
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return false;
		}
		else{
			$out = exec("cat /proc/loadavg");
			$values = array_map("trim", explode(" ",$out));
			return array('last1'=>$values[0],'last5'=>$values[1],'last15'=>$values[2]);
		}
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
	
	static function getQemuRamTemp(){
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return false;
		}
		else{
			return FileSystem::getDirectorySize('/dev/shm');
		}
	}
	
	static function getUSBDevices($forceReload = false){
		$cache = $GLOBALS['rootDir'].self::$cacheDir."/usbdevices";
		if(file_exists($cache) && filemtime($cache) > time()-60*5 && $forceReload == false){
			return unserialize(file_get_contents($cache));
		}
		else{
			$ret = array();
			exec("lsusb",$output);
			foreach($output as $line){
				preg_match("/ID (.*?):([0-9]*) (.*?)$/si", $line,$matches);
				$venID = $matches[1];
				$devID = $matches[2];
				$name  = $matches[3];
				if(!stristr($name,"hub")){
					$ret[] = array($venID,$devID,$name);
				}
			}
			file_put_contents($cache, serialize($ret));
			return $ret;
		}
	}
	
	static function toBytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
	
		return $val;
	}
	
}