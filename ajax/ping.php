<?php 
include '../config.php';
include '../classes/Helper.class.php';
Helper::loadClasses();

if(isset($_SESSION['user'])){
	
	$query = $GLOBALS['pdo']->prepare("UPDATE vm SET last_ping = NOW() WHERE owner = :owner AND status= :status AND persistent= :persistent");
	$query->bindValue(":owner",$_SESSION['user']->id,PDO::PARAM_INT);
	$query->bindValue(":status",QemuMonitor::RUNNING,PDO::PARAM_INT);
	$query->bindValue(":persistent",0,PDO::PARAM_INT);
	$query->execute();
}