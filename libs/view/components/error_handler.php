<?

class ErrorHandler implements ControlHandler {
	
	public function start_control($compiler, $node) {
		
		$memberName = $node->getAttribute("name");
		
		echo "<?";
		?>
		if(!empty($MVC_CURRENT_MODEL->errors["<?=$memberName?>"])) {
			echo '<div class="mvcModelError">' . $MVC_CURRENT_MODEL->errors["<?=$memberName?>"] . '</div>';
		}	
		<?	
		echo "?>";
		
		return false;
		
	}
	
	public function end_control() {
		
	}
	
}

?>