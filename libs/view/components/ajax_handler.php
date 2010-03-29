<?php

class AjaxHandler implements ControlHandler {
	
	public function start_control($compiler, $node) {
		
		/* Generate the controller based on the code */
		$code = ViewCompiler::collect($node);
		$path = ControllerGenerator::generateBasicController($compiler->controller, $compiler->method, $code);
		
		// If we don't have a parent, we create a hidden DIV that we can use to find our parent
		// and attach result to the parent of that div
		$divName = "mvcHiddenDiv" . time() . rand(100,999);
		if(!$node->hasAttribute("target")) {
			?>
			<div id="<?=$divName?>" style="display: none"></div>
			<?
		}
		
		/* 
		 * Create JavaScript -functions to invoke this Ajax-script. If we have execute-on -defined, we automatically
		 * create a <script>-wrapper and a function, if not, we assume the tag's inside a <script>-tag
		 */
		if($node->hasAttribute("execute-on")) {
			ob_start();
		}
		?>
		$.post('<?=$path?>', 
			{
				<?
				/*
				 * See which JS -variables to export to the PHP -script. If the code automatically generates
				 * a function, then we export any parameters, if it's embedded in JS -code, we can
				 * define the exported variables with the "export" parameter. Both expect a comma-separated
				 * list
				 */
				$exportVariables = ($node->hasAttribute("parameters") ? explode(", ", $node->getAttribute("parameters")) :
							($node->hasAttribute("export") ? explode(",", $node->getAttribute("export")) : array()));
				if(!empty($exportVariables)) {
					foreach($exportVariables as &$param) {
						$param = "'$param': $param";
					}
					echo join($exportVariables, ",");
				}
				?>
			}, function(data) {
			<?if($node->hasAttribute("target")):?>
				$('<?=$node->getAttribute("target")?>').append(data);
			<?else:?>
			 	$('#<?=$divName?>').parent().append(data)
			 	$('#<?=$divName?>').remove();
			<?endif;?>
		});
		<?
		/*
		 * If we define the "execute-on" -parameter, then we create a function automatically.
		 * 
		 * TODO: Would be nice if this could be automatically bound to events, e.g:
		 * 
		 * execute-on="$('#foo').blur"
		 */
		if($node->hasAttribute("execute-on")) {
			$script = ob_get_contents();
			ob_end_clean();
			?>
			<script language="JavaScript" type="text/javascript">
			<!--
			function <?=$node->getAttribute("execute-on")?>(<?=$node->getAttribute("parameters")?>) {
				<?=$script?>
				
			}
			//-->
			</script>
			<?
		}
		
		return false;
		
	}
	
	public function end_control() {
		
	}
	
}

?>
