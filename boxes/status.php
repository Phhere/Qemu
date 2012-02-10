<?php
$get = mysql_query("SELECT COUNT(vmID),status FROM vm GROUP BY status");
while($ds = mysql_fetch_assoc($get)){
	print_r($ds);
}