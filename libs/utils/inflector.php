<?php

class Inflector {
	
	/**
	 * The camelize() function changes things to camelcase.
	 * E.g. foo_and_bar -> FooAndBar
	 */
	public static function camelize($string) {

		$parts = explode("_", $string);
		foreach($parts as &$part) {
			$part = ucfirst(strtolower($part));
		}
		return join($parts);
		
	}
	
	/**
	 * Does the reverse of camelize() by converting FooAndBar to foo_and_bar
	 */
	public static function decamelize($string) {
		
		$res_string = strtolower(substr($string, 0, 1));
		for($i = 1; $i < strlen($string); $i++) {
			
			if(strtoupper($string[$i]) == $string[$i]) {
				$res_string .= "_";
			}
			
			$res_string .= strtolower($string[$i]);
			
		}
		
		return $res_string;
		
	}
	
}

?>
