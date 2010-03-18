<?php

class FoobarFilter extends Filter {
	
	public function processFilter($script, $function, $parameters, $input) {
		$input["ding"] = $input["ding"] . " (" . $parameters["param"] . ")";
	}
		
}

?>
