<?php

class InputHandler implements ControlHandler {
	
	public function start_control($compiler, $node) {

		echo "<?";		
		?>
		if(!($_formControlModel->modelCache->hasField('<?=$node->getAttribute("name")?>'))) {
			echo '<div class="mvcControlWarning" style="color: red">Warning, model ' . $_formControlModel->modelName . ' has no member ' .
					'named <?=$node->getAttribute("name")?></div>';
		}
		<?
		echo "?>";
		
		/*
		 * If the node has children, then we assume that a label is also defined in the tag and we create the div and all. 
		 * If not, we create just the input -elemnt
		 */
		if($node->hasChildNodes()) {
			echo $this->html->element(
				"div",
					$this->html->element("label", ViewCompiler::collect($node), array("for" => $node->getAttribute("name"))) .
					$this->generateErrorCode($node->getAttribute("name")) . 
					$this->html->element("input", null, ViewCompiler::attributesAsArray($node->attributes)), 
				array("class" => "mvcInput", "id" => "inputFld" . $node->getAttribute("name")));
		} else {
			echo $this->html->element("input", null, ViewCompiler::attributesAsArray($node->attributes));			
		}
				
		return false;
		
	}
	
	public function generateErrorCode($memberName) {
		echo "<?";
		?>
		if(!empty($MVC_CURRENT_MODEL->errors["<?=$memberName?>"])) {
			echo '<div class="mvcModelError">' . $MVC_CURRENT_MODEL->errors["<?=$memberName?>"] . '</div>';
		}	
		<?
		echo "?>";
	}
	
	public function end_control() { }
	
}

?>
