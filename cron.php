<?php 
include './config.php';
include './classes/Helper.class.php';

Helper::loadClasses();

$query = $GLOBALS['pdo']->prepare("SELECT * FROM vm WHERE status=:status AND persistent=:persistent");
$query->bindValue(":status",QemuMonitor::RUNNING,PDO::PARAM_INT);
$query->bindValue(":persisent",0,PDO::PARAM_INT);
$query->execute();

while($ds = $query->fetch()){
	$last_ping = strtotime($ds['last_ping']);
	if($last_ping < time() - (int)$GLOBALS['config']['ping_timeout']){
		$vm = new QemuVm($ds['vmID']);
		try{
			$vm->connect();
		}
		catch(Exception $e){
			//$tmp->assign('message',"<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>");
			$vm->setStatus(QemuMonitor::SHUTDOWN);
		}
		if(!isset($e)){
			$vm->shutdown();
			//$tmp->assign('message',"<div class='notice success'>Die VM wird ausgeschaltet.</div>");
		}
	}
}
$query = null;