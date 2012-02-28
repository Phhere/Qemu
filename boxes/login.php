<?php
if(isset($_SESSION['user'])){
	$GLOBALS['template']->assign('box_login',$GLOBALS['template']->draw('loggedin',true));
}
else{
	$GLOBALS['template']->assign('box_login',$GLOBALS['template']->draw('login',true));
}