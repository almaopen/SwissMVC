<?php

class Cache {
	
	public static function get($key) {
		if(Cache::$_selfInstance == null) {
			Cache::init();
		}
		return Cache::$_selfInstance->_mc->get($key);
	}
	
	public static function set($key, $item, $expire = false, $timeout = 3600) {
		if(Cache::$_selfInstance == null) {
			Cache::init();
		}	
		Cache::$_selfInstance->_mc->set($key, $item, true, ($expire ? $timeout : 0));
	}
	
	public static function delete($key) {
		if(Cache::$_selfInstance == null) {
			Cache::init();
		}	
		Cache::$_selfInstance->_mc->delete($key);
	}
	
	/**
	 * Singleton stuff 
	 */
	
	private static $_selfInstance = null;
	 
	private $_mc = null;
	
	private static function init() {
		Cache::$_selfInstance = new Cache();
	}
	 
	private function Cache() {
		$this->_mc = new Memcache();
		$this->_mc->connect(AppConfiguration::$CACHE_SERVER['cache.host'], AppConfiguration::$CACHE_SERVER['cache.port']);
		$this->_mc->flush();
	}
	
	
}

?>
