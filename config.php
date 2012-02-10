<?php

mysql_connect("localhost","user","pass");
mysql_select_db("qemu");

$GLOBALS['config'] = array();
$get = mysql_query("SELECT * FROM config");
while($ds = mysql_fetch_assoc($get)){
	$GLOBALS['config'][$ds['key']] = $ds['value'];
}
?>