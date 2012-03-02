<?php 
include '../config.php';
include '../classes/Helper.class.php';
Helper::loadClasses();

if(isset($_SESSION['user'])){
	mysql_query("UPDATE vm SET last_ping = NOW() WHERE owner = '".$_SESSION['user']->id."' AND status='".QemuMonitor::RUNNING."' AND persistent='0'");
}