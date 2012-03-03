<?php
include './config.php';
include './classes/Helper.class.php';

Helper::loadClasses();

if(isset($_SESSION['user'])){
	$vmID = (int) $_GET['vmID'];

	$vm = new QemuVm($vmID);
	if(Helper::isOwner($_GET['vmID'])){
		if($vm->status == QemuMonitor::RUNNING){
			$vm->connect();
			$path = $GLOBALS['config']['screenshot_dir'].'/vm_'.$vm->vmID.'_'.date("Y_m_d_h_i_s");
			$screen = $vm->createScreenshot($path);
			if($screen){
				header("Content-Type: image/png");
				echo file_get_contents($path.".png");
			}
			else{
				echo "Fehler";
			}
		}
	}
}