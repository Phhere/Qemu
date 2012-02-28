<?php
if(isset($_SESSION['user'])){
	unset($_SESSION['user']);
	session_destroy();
}
$GLOBALS['template']->assign('content',$GLOBALS['template']->draw('logout',true));