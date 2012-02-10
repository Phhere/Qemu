<?php
error_reporting(E_ALL);
ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) {
	ob_end_flush();
}
ob_implicit_flush(1);
include 'classes/QemuMonitor.class.php';
include 'classes/PPM.class.php';
echo "<pre>";

$monitor = new QemuMonitor("localhost", 5001);

$monitor->execute("info vnc");

var_dump($monitor->getResponse());
$monitor->__destruct();