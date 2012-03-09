<?php
$GLOBALS['template']->assign('box_ping','');
if(isset($_SESSION['user'])){

	$query = $GLOBALS['pdo']->prepare("SELECT count(*) AS `running` FROM vm WHERE owner = :owner AND status = :status  AND persistent = :persistent");
	$query->bindValue(":owner",$_SESSION['user']->id,PDO::PARAM_INT);
	$query->bindValue(":status",QemuMonitor::RUNNING,PDO::PARAM_INT);
	$query->bindValue(":persistent",0,PDO::PARAM_INT);
	$query->execute();

	$data = $query->fetch();

	if($data['running']>0){
		$GLOBALS['template']->assign('box_ping','<script type="text/javascript">window.ping=true;</script>');
	}
}
?>