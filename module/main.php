<?php
if(isset($_GET['action'])){
	$action = $_GET['action'];
}
else {
	$action = null;
}
function execute($cmd){

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$WshShell = new COM("WScript.Shell");
		$oExec = $WshShell->Run($cmd, 0, false);
	}
	else{
		exec( $cmd." > /dev/null &");
	}
}
?>
Main Seite
<a href="index.php?site=main&action=start">Start VM</a><br/>
<a href="index.php?site=main&action=stop">Stop VM</a>

<?php
$vm = new QemuVm(1);
if($action == "start"){
	$vm->startVM();
}
if($vm->status == QemuMonitor::RUNNING){
	try{
		$vm->connect();
		if($action == "stop"){
			$vm->shutdown();
		}
		$vm->getBlockDevices();
	}
	catch(Exception $e){
		echo "VM scheint offline zu sein";
	}
	print_r($vm);
}
?>