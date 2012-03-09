<?php
abstract class Singleton {
	private static $__instance;
	protected function __construct(){
		
	}
	final private function __clone(){
	}
	final public static function getInstance(){
		if(self::$__instance == NULL){
			$class = get_called_class();
			self::$__instance = new $class;
		}
		return self::$__instance;
	}
}