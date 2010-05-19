<?

class ControllerErrors {
	
	public static function missingController($Controller) {
		$error_description = "Misssing Controller-class for Controller $Controller (Request URI: " . $_SERVER['REQUEST_URI'] . ")";
		$error_fix = "<p>Create the file webapp/controllers/${Controller}_controller.php with the following contents:<br/><br/>" .
			"<pre>class " . strtoupper(substr($Controller, 0, 1)) . substr($Controller, 1) . "Controller extends Controller {<br/><br/>" .
			"&nbsp;&nbsp;// Controller methods<br/><br/>}</pre></p>";
		$title_for_page = "Missing Controller";
		$show_backtrace = false;		
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();
	}
	
	public static function missingMethod($Controller, $method) {
		$error_description = "Misssing method $method in " . ucfirst($Controller) . "Controller (Request URI: " . $_SERVER['REQUEST_URI'] . ")";
		$error_fix = "<p>Add the method below to webapp/controllers/${Controller}_controller.php:<br/><br/>" .
			"<pre>class " . strtoupper(substr($Controller, 0, 1)) . substr($Controller, 1) . "Controller extends Controller {<br/><br/>" .
			"&nbsp;&nbsp;public function $method() {<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;// ...<br/><br/>&nbsp;&nbsp;}<br/><br/>}</pre></p>";
		$title_for_page = "Missing method";
		$show_backtrace = false;
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();

	}
	
	public static function missingView($Controller, $method, $view) {
		$error_description = "Misssing view for " . strtoupper(substr($Controller, 0, 1)) . substr($Controller, 1) . "::$method " .
					" (Request URI: " . $_SERVER['REQUEST_URI'] . ")";
		$error_fix = "<p>Create the file $view</p>";
		$title_for_page = "Missing view";
		$show_backtrace = false;		
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();
	}
	
	public static function missingTemplate($templateFile) {
		$error_description = "Misssing template $templateFile.";
		$error_fix = "<p>Create the file $templateFile</p>";
		$title_for_page = "Missing template";
		$show_backtrace = false;		
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();
		
	}
	
}

?>
