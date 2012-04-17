<?php
if(isset($_SESSION['user'])){
	$GLOBALS['template']->assign('box_login',$GLOBALS['template']->draw('loggedin',true));
}
else{
	if(!isset($_SESSION['wrongLoginCount']) || $_SESSION['wrongLoginCount'] < 5){
		$GLOBALS['template']->assign('box_login',$GLOBALS['template']->draw('login',true));
	}
	else{
		$GLOBALS['template']->assign('box_login','Max 5 Wrong Logins');
	}
}