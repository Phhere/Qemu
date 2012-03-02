<?php 
include '../config.php';
include '../classes/Helper.class.php';
Helper::loadClasses();

if(isset($_SESSION['user'])){
	$usb_list = Helper::getUSBDevices(true);
	if(count($usb_list)){
		foreach(Helper::getUSBDevices() as $dev){
			$usb .= '<option value="'.$dev[0].':'.$dev[1].'">'.$dev[2].'</option>';
		}
	}
	else{
		$usb = '<option value="0">kein USB Gerät vorhanden</option>';
	}
	echo $usb;
}