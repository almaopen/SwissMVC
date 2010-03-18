<?php

class SimpleMVCErrors {
	
	public static function generalError($error) {
		ob_end_clean();
		$error_description = $error;
		$title_for_page = "General Application Error";
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();		
	}
	
	public static function sqlError($query, $error) {
		ob_end_clean();		
		$error_description = "Database backend gave an error";
		$details = "Error when performing query.<br/>SQL Query: $query<br/>Response: $error";
		require_once(LIBS_ROOT . "/templates/error_template.php");
		exit();
	}
	
	
}

?>
