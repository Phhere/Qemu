<?php
class Routing extends Singleton{
	private $routes = array();
	private $default_page = "start";
	private $default_action = "default";
	private $append_render = array();
	public function addRoute(Modul $modul, $site, $method, $post_field = null){
		if(!isset($this->routes[$site])){
			$this->routes[$site] = array();
		}
		$this->routes[$site][] = array($modul,$method,$post_field);
	}

	public function addRouteByAction(Modul $modul, $site, $method){
		$this->addRoute($modul, $site, $method);
	}

	public function addRouteByPostField(Modul $modul, $site, $method, $post_field){
		$this->addRoute($modul, $site, $method,$post_field);
	}

	public function appendRender(Modul $modul, $method){
		$this->append_render[] = array($modul,$method);
	} 
	
	public function render(){
		if(isset($_GET['action'])){
			$action = $_GET['action'];
		}
		else{
			$action = null;
		}

		if(isset($GLOBALS['site']) ){
			$site = $GLOBALS['site'];
		}
		else{
			$site = $this->default_page;
		}

		$content = '';
		$found = false;
		$module = null;
		if(isset($this->routes[$site])){
			foreach($this->routes[$site] as $route){
				if(isset($_POST[$route[2]])){
					$found = true;
					$module = $route[0];
					$content .= call_user_func(array($route[0],"post_".$route[1]));
				}
				elseif($route[1] == $action || ($route[1] == $this->default_action && $found == false && count($this->append_render) == 0)){
					$found = true;
					$module = $route[0];
					$content .= call_user_func(array($route[0],"action_".$route[1]));
				}
			}
		}
		
		if($found == false){
			$content = '<h3>Fehler</h3><div class="notice error">Unbekannte Seite / Aktion</div>';
		}
		else{
			foreach($this->append_render as $obj){
				$content .= call_user_func($obj);
			}
		}
		if($module != null){
			$content = $module->getHeader().$content;
		}
		return $content;
	}
}