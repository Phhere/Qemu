<?php 
class Downloads extends Modul{
	
	public function getHeader(){
		return "<h1>Downloads</h1>";
	}
	
	public function action_default(){
		$tmp2 = new RainTPL();
		return $tmp2->draw('downloads',true);
	}
	
}
Routing::getInstance()->addRouteByAction(new Downloads(),'downloads','default');
?>