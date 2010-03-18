<?php

class FormHandler implements ControlHandler {
	
	public function start_control($compiler, $node) {
		
		echo "<?\n";
		?>
		$_formControlModel = Model::getModel('<?=$node->getAttribute("model");?>');
		if(!isset($MVC_CURRENT_MODEL)) {
			$MVC_CURRENT_MODEL = $_formControlModel;
		}
		<?
		echo "\n?>";
		
		$parameters = ViewCompiler::attributesAsArray($node->attributes);
		
		/*
		 * See if we need to automatically create a controller for this save action
		 */
		if($node->getAttribute("autocreate-controller") == "true") {
			
			$target = ControllerGenerator::generateSaveController
				(
					$compiler->controller, 
					$compiler->method, 
					$node->getAttribute("model"),
					$node->getAttribute("redirect")
				);
				
			$parameters["method"] = "post";
			$parameters["action"] = $target;
			
		}
		
		unset($parameters["model"]);
		unset($parameters["redirect"]);
		unset($parameters["autocreate-controller"]);
		
		echo $this->html->startElement("form", $parameters);
		
		return true;
	}
	
	public function end_control() {
		echo "</form>";
	}
	
}
?>
