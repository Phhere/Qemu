<?php
class QemuVm {
	public $monitor;
	private $devices;
	public $status;
	protected $host;
	protected $monitor_port;
	public $ram;
	public $images;
	public $vmID;
	public $vnc_port;
	public $owner;
	public function __construct($id){
		$this->monitor = null;
		$this->devices = array();
		$this->host = "localhost";
		$this->vmID = $id;
		$this->images = array();
		
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM vm WHERE vmID= :vmID");
		$query->bindValue(':vmID', $this->vmID, PDO::PARAM_INT);
		$query->execute();
		
		if($query->rowCount()){
			$data = $query->fetch();
			$this->ram = $data['ram'];
			$this->monitor_port = $GLOBALS['config']['monitorport_min'] + (int)$data['vmID'];
			$this->vnc_port = $GLOBALS['config']['vncport_min'] + (int)$data['vmID'];
			$this->status = $data['status'];
			$this->name = $data['name'];
			$this->password = $data['password'];
			$this->owner = $data['owner'];
			
			$query2 = $GLOBALS['pdo']->prepare("SELECT *,i.path,i.type FROM vm_images v JOIN images i ON i.imageID=v.imageID WHERE v.vmID = :vmID");
			$query2->bindValue(':vmID', $this->vmID, PDO::PARAM_INT);
			$query2->execute();
			
			while($ds = $query2->fetch()){
				$this->images[] = array('path'=>$ds['path'],'type'=>$ds['type']);
			}
		}
		else{
			throw new Exception("Unkown VM ID");
		}
	}

	/**
	 * Build the command and start the vm
	 */
	public function startVM(){
		$cmd = $GLOBALS['config']['qemu_executable'];
		if(!empty($GLOBALS['config']['qemu_bios_folder'])){
			$cmd .=" -L ".$GLOBALS['config']['qemu_bios_folder'];
		}
		$cmd .=" -m ".$this->ram;
		
		$usb = false;
		foreach($this->images as $image){
			if($image['type'] == "usb"){
				$usb = true;
				$cmd .=" -usbdevice host:".$image['path'];
			}
			else{
				$cmd .=" -".$image['type']." ".$image['path'];
			}
		}
		
		if($usb){
			$cmd .= ' -usb';
		}
		
		$cmd .=" -localtime";
		$cmd .=" -monitor telnet:localhost:".$this->monitor_port.",server,nowait";
		$cmd .=" -vnc :".$this->vmID.",password";
			
		$this->executeStart($cmd);
		$query = $GLOBALS['pdo']->prepare("UPDATE vm SET lastrun=NOW(),last_ping=NOW() WHERE vmID= :vmID");
		$query->bindValue(':vmID', $this->vmID, PDO::PARAM_INT);
		$query->execute();
		
		if($this->password != ""){
			usleep(100000);
			$this->connect();
			$this->setVncPassword($this->password);
		}
	}
	
	/**
	 * Set the status of the VM
	 * @param int $status
	 */
	public function setStatus($status){
		$this->status = $status;
		$query = $GLOBALS['pdo']->prepare("UPDATE vm SET status=:status WHERE vmID= :vmID");
		$query->bindValue(':vmID', $this->vmID, PDO::PARAM_INT);
		$query->bindValue(':status', $status, PDO::PARAM_INT);
		$query->execute();
	}
	
	/**
	 * Connect to the Monitor
	 * if needed
	 */
	public function connect(){
		$this->monitor = new QemuMonitor($this->host, $this->monitor_port);
		$this->setStatus(QemuMonitor::RUNNING);
	}
	/**
	 * Get the current status from Qemu
	 * Return the status
	 * @return running, stopped, paused
	 */
	public function getStatus(){
		if($this->monitor){
			$this->monitor->execute("info status");
			$lines = explode("\n",$this->monitor->getResponse());
			foreach($lines as $line){
				if(strstr($line, ":")){
					list(,$status) = explode(": ",$line);
					$this->setStatus(trim($status));
					break;
				}
			}
			return $this->status;
		}
	}
	/**
	 * Reads all Block Devices (hdd,cd,..) from Qemu
	 */
	public function getBlockDevices(){
		if($this->monitor){
			$this->monitor->execute("info block");
			$lines = explode("\n",$this->monitor->getResponse());
			foreach($lines as $line){
				if(strstr($line, ":")){
					list($dev,$param) = explode(":",$line);
					$this->devices[trim($dev)] = trim($param);
				}
			}
		}
	}
	/**
	 * Pauses the Emulation
	 */
	public function stop(){
		$this->monitor->execute("stop");
		$this->getStatus();
	}
	/**
	 * Resums the Emulation
	 */
	public function resume(){
		$this->monitor->execute("cont");
		$this->getStatus();
	}

	/**
	 * Shuts down the emulation
	 */
	public function shutdown(){
		$this->monitor->execute("system_powerdown");
		usleep(3000);
		$this->monitor->execute("quit");
		$this->setStatus(QemuMonitor::SHUTDOWN);
	}

	/**
	 * Eject a Device from Emulation
	 * @param string $device String from getBlockDevice
	 * @param boolean $force Force to remove the device
	 * @throws Exception
	 */
	public function ejectDevice($device,$force = false){
		if(in_array($device,$this->devices)){
			if($force){
				$this->monitor->execute("eject -f ".$device);
			}
			else{
				$this->monitor->execute("eject ".$device);
			}
		}
		else{
			throw new Exception("Unkown device. Maybe getBlockDevices not called");
		}
	}

	/**
	 * Set a new File for Device
	 * @param string $device
	 * @param string $file Path to iso
	 */
	public function changeDevice($device,$file){
		$this->monitor->execute("change ".$device." ".$file);
	}

	/**
	 * Set the VNC connection password
	 * @param string $password The new password
	 */
	public function setVncPassword($password){
		$this->monitor->execute("set_password vnc ".$password);
	}

	/**
	 * Create a Screenshot from the VM
	 * @param string $filename Path of the PNG to save
	 */
	public function createScreenshot($filename){
		$this->monitor->execute("screendump ".$filename.".ppm");
		$ppm = new PpmImageReader();
		$img = $ppm->read($filename.".ppm");
		if(is_resource($img[1])){
			unlink($filename.".ppm");
			return imagepng($img[1],$filename.".png",4);
		}
		else{
			return false;
		}
	}
	/**
	 * Execute the Start Command and run in background without waiting for the output
	 * @param String $cmd
	 */
	private function executeStart($cmd){
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$cmd = "cmd /c ".$cmd." >> ".chr(34).$GLOBALS['config']['log_path'].'\vm_'.$this->vmID."_".date("Y_m_d").".log".chr(34)." 2>&1";
			$WshShell = new COM("WScript.Shell");
			$WshShell->Run($cmd, 0, false);
		}
		else{
			$cmd = $cmd." > ".$GLOBALS['config']['log_path'].'/vm_'.$this->vmID."_".date("Y_m_d").".log &";
			exec($cmd);
		}
	}
	
	/**
	 * Checks if current User is owner 
	 * or current User is admin
	 */
	public function isOwner(){
		if(isset($_SESSION['user'])){
			if($this->owner == $_SESSION['user']->id){
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
}