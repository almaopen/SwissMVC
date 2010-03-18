<?php

class PasswordHandler implements ControlHandler {
	
	public function start_control($compiler, $node) {

		echo "<?";		
		?>
		if(!($_formControlModel->modelCache->hasField('<?=$node->getAttribute("name")?>'))) {
			echo '<div class="mvcControlWarning" style="color: red">Warning, model ' . $_formControlModel->modelName . ' has no member ' .
					'named <?=$node->getAttribute("name")?></div>';
		}
		<?
		echo "?>";
		
		$labelNodes = $node->getElementsByTagNameNS("http://simplemvc.org/ns/2010", "label");
		$labels = array();
		for($i = 0; $i < $labelNodes->length; $i++) {
			$label = $labelNodes->item($i);
			$labels[$label->getAttribute("for")] = ViewCompiler::collect($label);
		}
		
		
		?>
		<div class="mvcInput" id="inputFld<?=$node->getAttribute("name")?>">
		 <label for="<?=$node->getAttribute("name")?>"><?=$labels["password"]?></label>
		 <input type="password" name="<?=$node->getAttribute("name")?>"
	 			/>
	    </div>
		<div class="mvcInput" id="inputFld<?=$node->getAttribute("name")?>_verify">
		 <label for="<?=$node->getAttribute("name")?>"><?=$labels["password_verify"]?></label>
		 <input type="password" name="<?=$node->getAttribute("name")?>_verify"
	 			/>
	    </div>	    
		<?
		
		return false;
		
	}
	
	public function end_control() { }
	
}


?>
