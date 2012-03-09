<?php
class Server {
	/**
	 * Check ob noch Ram frei ist und noch nicht zu viele VMs laufen
	 * @param int $ram_needed Benötigter Ram
	 */
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

	/**
	 * 
	 */
	static function getRamSettings(){
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return false;
		}
		else{
			$out = exec("free",$lines);
			foreach($lines as $index => $line){
				$parts = array_values(array_filter(array_map("trim", explode(" ",$line))));
				if($index == 1){
					$all = $parts[1]*1024;
				}
				if($index == 2){
					$used = $parts[2]*1024;
					$free = $parts[3]*1024;
				}
			}
			return array('all'=>$all,'free'=>$free,'used'=>$used);
		}
	}
	
	/**
	 * CPU Last der letzten 1,5,15 Minuten auslesen
	 */
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
	
	/**
	 * Größe von /dev/shm, da von Qemu genutzt
	 */
	static function getQemuRamTemp(){
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return false;
		}
		else{
			return FileSystem::getDirectorySize('/dev/shm');
		}
	}

	
	/**
	 * Liest alle vorhanden USB-Gerät aus und
	 * gibt Array mit VendorID, DeviceID und Name zurück
	 * @param boolean $forceReload Cache ignorieren
	 */
	static function getUSBDevices($forceReload = false){
		$cache = $GLOBALS['config']['cache_dir']."/usbdevices";
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

	/**
	 * Gibt den Namen eines USB-Geräts zurück
	 * @param String $identifier
	 * @return string
	 */
	static function getUsbDeviceName($identifier){
		$devices = self::getUSBDevices();
		foreach($devices as $device){
			if($device[0].":".$device[1] == $identifier){
				return $device[2];
			}
		}
		return "unknown";
	}
}