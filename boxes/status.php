<?php
$get = mysql_query("SELECT count(vmID) AS `vms`, SUM(IF(status=1,1,0)) AS `vms_on` FROM vm");
$data = mysql_fetch_assoc($get);
$GLOBALS['template']->assign('box_status',"VMs: ".$data['vms']."<br/>VMs online:".$data['vms_on']);