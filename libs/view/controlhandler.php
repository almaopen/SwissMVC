<?php

interface ControlHandler {
	
	public function start_control($compiler, $node);
	
	public function end_control();
	
}

?>
