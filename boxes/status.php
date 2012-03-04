<?php
$query = $GLOBALS['pdo']->prepare("SELECT count(vmID) AS `vms`, SUM(IF(status=1,1,0)) AS `vms_on` FROM vm");
$query->execute();

$data = $query->fetch();
$GLOBALS['template']->assign('box_status',"VMs: ".$data['vms']."<br/>VMs online:".$data['vms_on']);