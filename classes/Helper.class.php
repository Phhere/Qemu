<?php
class Helper {

	static function loadClasses(){
		foreach(glob("classes/*.php") as $file){
			require_once $file;
		}
	}

	static function isOwner($vmID){
		if(isset($_SESSION['user'])){
			$get = mysql_query("SELECT owner FROM vm WHERE vmID = '".$vmID."' AND owner = '".$_SESSION['user']->id."'");
			if(mysql_num_rows($get)){
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
		$get = mysql_query("SELeCT count(vmId) AS `running` FROM vm WHERE status = '".QemuMonitor::RUNNING."'");
		$data = mysql_fetch_assoc($get);
		
		$run = true;
		
		if($data['running'] > $GLOBALS['config']['max_running_vms']){
			$run = false;
		}
		
		/*$ram = self::getQemuRamTemp();
		if($ram != false && $ram + $ram_needed  > $GLOBALS['config']['max_ram']*1024*1024*1024){
			$run = false;
		}*/
		
		return $run;
	}
	
	static function getUserName($roleID){
		$get = mysql_query("SELECT email FROM users WHERE userID='".$roleID."'");
		$data = mysql_fetch_assoc($get);
		return $data['email'];
	}
	
	static function getRoleName($roleID){
		$get = mysql_query("SELECT name FROM roles WHERE roleID='".$roleID."'");
		$data = mysql_fetch_assoc($get);
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
	
}