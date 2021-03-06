<?php
session_start();

try{
	$dsn = 'mysql:dbname=qemu;host=localhost';
	$user = 'user';
	$password = 'pass';
	$GLOBALS['pdo'] = new PDO($dsn, $user, $password);
}
catch(Exception $e){
	die($e);
}

$GLOBALS['config'] = array();
foreach($GLOBALS['pdo']->query("SELECT * FROM config") as $ds){
	$GLOBALS['config'][$ds['key']] = $ds['value'];
}

$GLOBALS['device_types']  = '<option value="cdrom">CD-ROM</option>';
$GLOBALS['device_types'] .= '<option value="hda">HDD A</option>';
$GLOBALS['device_types'] .= '<option value="hdb">HDD B</option>';
$GLOBALS['device_types'] .= '<option value="hdC">HDD C</option>';
$GLOBALS['device_types'] .= '<option value="floppy">Floppy</option>';
$GLOBALS['device_types'] .= '<option value="usb">USB</option>';

$GLOBALS['hdd_formats']  = '<option value="raw">Raw</option>';
$GLOBALS['hdd_formats'] .= '<option value="qcow2">QCow2</option>';
$GLOBALS['hdd_formats'] .= '<option value="vmdk">VMware (vmdk)</option>';
$GLOBALS['hdd_formats'] .= '<option value="vdi">VirtualBox (vdi)</option>';

$GLOBALS['rootDir'] = realpath(__DIR__);
?>