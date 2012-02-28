<?php 
include 'config.php';

include 'classes/Helper.class.php';

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

include 'boxes/login.php';
include 'boxes/status.php';
include 'boxes/navigation.php';

$GLOBALS['template']->assign('box_news',file_get_contents('boxes/news.php'));
$GLOBALS['template']->draw( "index" );
?>