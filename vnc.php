<?php 
session_start();
include 'config.php';

include 'classes/Image.class.php';
include 'classes/QemuMonitor.class.php';
include 'classes/PPM.class.php';
include 'classes/QemuVm.php';
include 'classes/User.class.php';
include 'classes/login.php';

if(isset($_SESSION['user'])){
	$vmID = $_GET['vmID'];
	if(isOwner($_GET['vmID'])){
		$vm = new QemuVm($_GET['vmID']);
		if($vm->status == QemuMonitor::RUNNING){
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=VM_".str_replace(" ","_",$vm->name).".vnc");
			header("Content-Type: application/zip");
			header("Content-Transfer-Encoding: binary");
			echo '[connection]
host=192.168.178.21
port='.$vm->vnc_port.'
[options]
viewonly=0
fullscreen=0
shared=1
belldeiconify=0
disableclipboard=0
restricted=0
swapmouse=0
emulate3=1
fitwindow=0
cursorshape=1
noremotecursor=0
preferred_encoding=7
compresslevel=-1
quality=6
emulate3timeout=100
emulate3fuzz=4
localcursor=1
scale_den=1
scale_num=1
local_cursor_shape=1';
		}
	}
}
?>