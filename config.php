<?php
session_start();

mysql_connect("localhost","user","pass");
mysql_select_db("qemu");

$GLOBALS['config'] = array();
$get = mysql_query("SELECT * FROM config");
while($ds = mysql_fetch_assoc($get)){
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