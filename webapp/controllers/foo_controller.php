<?php

class FooController extends Controller {
	
	public function bar() {
		$this->set("ding", $this->input["ding"]);
	}
	
	public function ding() {
		
	}
	
}

?>
