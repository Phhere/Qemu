<?php 
class Logout extends Modul{
	public function getHeader(){
		return "<h1>Logout</h1>";
	}
	
	public function action_default(){
		if(isset($_SESSION['user'])){
			unset($_SESSION['user']);
			session_destroy();
		}
		
		$tmp2 = new RainTPL();
		return $tmp2->draw('logout',true);
	}
	
}
Routing::getInstance()->addRouteByAction(new Logout(),'logout','default');
?>