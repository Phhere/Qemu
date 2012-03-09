<?php 
class Start extends Modul{
	public function getHeader(){
		return "<h1>Willkommen</h1>";
	}
	public function action_default(){
		$tmp2 = new RainTPL();
		return $tmp2->draw('start',true);
	}
	
}
Routing::getInstance()->addRouteByAction(new Start(),'start','default');
?>