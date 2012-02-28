<?php

function getNaviClass($pages){
	return (in_array($GLOBALS['site'],$pages)) ? 'current' : '';
}
$tmp = $GLOBALS['template'];
$tmp->assign('class_main',getNaviClass(array("","start")));
$tmp->assign('class_myvm',getNaviClass(array("myvm")));
$tmp->assign('class_verwaltung',getNaviClass(array("images","vms","users","system")));
$tmp->assign('class_vms',getNaviClass(array("vms")));
$tmp->assign('class_images',getNaviClass(array("images")));
$tmp->assign('class_user',getNaviClass(array("users")));
$tmp->assign('class_system',getNaviClass(array("system")));
$tmp->assign('class_downloads',getNaviClass(array("downloads")));

$GLOBALS['template']->assign('box_navi',$tmp->draw('navigation',true));