<?

class ControllerErrors {
	
	public static function missingController($action) {
		$error_description = "Misssing Controller-class for action $action (Request URI: " . $_SERVER['REQUEST_URI'] . ")";
		$error_fix = "<p>Create the file webapp/controllers/${action}_controller.php with the following contents:<br/><br/>" .
			"<pre>class " . strtoupper(substr($action, 0, 1)) . substr($action, 1) . "Action extends Action {<br/><br/>" .
			"&nbsp;&nbsp;// Action methods<br/><br/>}</pre></p>";
		$title_for_page = "Missing action";
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();
	}
	
	public static function missingMethod($action, $method) {
		$error_description = "Misssing method for action $method in Action-class for action $action (Request URI: " . $_SERVER['REQUEST_URI'] . ")";
		$error_fix = "<p>Add the method below to webapp/actions/${action}_action.php:<br/><br/>" .
			"<pre>class " . strtoupper(substr($action, 0, 1)) . substr($action, 1) . "Action extends Action {<br/><br/>" .
			"&nbsp;&nbsp;public function $method() {<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;// ...<br/><br/>&nbsp;&nbsp;}<br/><br/>}</pre></p>";
		$title_for_page = "Missing method";
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();

	}
	
	public static function missingView($action, $method, $view) {
		$error_description = "Misssing view for " . strtoupper(substr($action, 0, 1)) . substr($action, 1) . "::$method " .
					" (Request URI: " . $_SERVER['REQUEST_URI'] . ")";
		$error_fix = "<p>Create the file $view</p>";
		$title_for_page = "Missing view";
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();
	}
	
	public static function missingTemplate($templateFile) {
		$error_description = "Misssing template $templateFile.";
		$error_fix = "<p>Create the file $templateFile</p>";
		$title_for_page = "Missing template";
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();
		
	}
	
}

?>
