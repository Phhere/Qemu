<?php 
include './config.php';
include './classes/Helper.class.php';

$GLOBALS['site'] = "start";
if(isset($_GET['site']) && is_string($_GET['site'])){
	$name = basename(preg_replace('/[^(\x20-\x7F)]*/', '',$_GET['site']));
	if(file_exists('./module/'.$name.'.php')){
		$GLOBALS['site'] = $name;
	}
}

Helper::loadClasses();

$GLOBALS['template'] = new raintpl();
raintpl::configure( 'tpl_dir','./templates/');
raintpl::configure( 'cache_dir','./cache/templates/');

include './module/'.$GLOBALS['site'].'.php';

$content = Routing::getInstance()->render();
$GLOBALS['template']->assign('content',$content);

include './boxes/login.php';
include './boxes/status.php';
include './boxes/navigation.php';
include './boxes/ping.php';

$GLOBALS['template']->assign('box_news',file_get_contents('boxes/news.php'));
$GLOBALS['template']->draw( "index" );
?>