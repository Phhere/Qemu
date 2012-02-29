<?php 
include './config.php';
include './classes/Helper.class.php';

$GLOBALS['site'] = "start";
if(isset($_GET['site'])){
	if(file_exists('./module/'.basename($_GET['site']).'.php')){
		$GLOBALS['site'] = basename($_GET['site']);
	}
}

Helper::loadClasses();

$GLOBALS['template'] = new raintpl();
raintpl::configure( 'tpl_dir','./templates/');
raintpl::configure( 'cache_dir','./cache/templates/');

include './module/'.$GLOBALS['site'].'.php';

include './boxes/login.php';
include './boxes/status.php';
include './boxes/navigation.php';

if(isset($_SESSION['user'])){
	$get = mysql_query("SELECT count(*) AS `running` FROM vm WHERE owner = '".$_SESSION['user']->id."' AND status='".QemuMonitor::RUNNING."' AND persistent='0'");
	$data = mysql_fetch_assoc($get);
	if($data['running']>0){
		$GLOBALS['template']->assign('ping','<script type="text/javascript">window.ping=true;</script>');
	}
}

$GLOBALS['template']->assign('box_news',file_get_contents('boxes/news.php'));
$GLOBALS['template']->draw( "index" );
?>