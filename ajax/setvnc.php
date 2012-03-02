<?php
include '../config.php';
include '../classes/Helper.class.php';
Helper::loadClasses();

if(isset($_SESSION['user'])){
	$vmID = (int) $_GET['vmID'];
	
	$password = $_GET['pass'];
	
	$vm = new QemuVm($vmID);
	if(Helper::hasRessources($vm->ram)){
		if(Helper::isOwner($_GET['vmID'])){
			if($vm->status == QemuMonitor::RUNNING){
				$vm->connect();
				$vm->setVncPassword($password);
				echo "OK";
				die();
			}
		}
	}
}
echo "ERROR";
?>