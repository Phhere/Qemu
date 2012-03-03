<?php 
include './config.php';
include './classes/Helper.class.php';

Helper::loadClasses();

$get = mysql_query("SELECT * FROM vm WHERE status='".QemuMonitor::RUNNING."' AND persistent='0'");
while($ds = mysql_fetch_assoc($get)){
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