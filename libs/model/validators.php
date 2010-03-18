<?php

/**
 * This class needs a little work...
 */
class Validator {
	
	public static function regex($str, $params = array()) {
		return preg_match("#" . $params["regex"] . "#", $str);
	}
	
	public static function mandatory($str, $params = array()) {
		return !empty($str);
	}
	
	public static function checkUnique($str, $params = array()) {
		
		$model = new Model($params["type"]);
		if(!is_array($params["conditions"])) $params["conditions"] = array();
		$item = $model->load(array("conditions" => array_merge($params["conditions"], array($params["field"] => $str))));
		return empty($item);
		
	}
	
	public static function minmax($str, $params = array()) {
		if(intval($str) < intval($params["min"]) || intval($str) > intval($params["max"]))
			return false;
		return true;
	}
	
	public static function length($str, $params = array()) {
		if(!empty($params["min"])) {
			if(strlen($str) < intval($params["min"])) {
				return false;
			}
		}
		if(!empty($params["max"])) {
			if(strlen($str) > intval($params["max"])) {
				return false;
			}
		}
		return true;
	}
	
}

?>
