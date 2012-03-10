<?php
abstract class Modul {
	abstract public function action_default();
	abstract public function getHeader();
	
	static public function hasAccess(){
		if(isset($_SESSION['user'])){
			$args = func_get_args();
			foreach($args as $arg){
				if(isset($_SESSION['user']->role[$arg]) && $_SESSION['user']->role[$arg] == 1){
					return true;
				}
			}	
			return $_SESSION['user']->role['system'] == 1;
		}
		
		return false;
	}
}