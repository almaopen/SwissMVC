<?php

/**
 * A View displays the variables set with by the Action
 * 
 * It's a really simple class ;)
 * 
 */
class View {
	
	/**
	 * The path to this view
	 */
	public $path = null;
	
	public function View($viewPath) {
		$this->path = $viewPath;
	}
	
	public function render($vars, $contoller, $function) {
		
		$this->html = new HtmlHelper($contoller);
		
		extract($vars);
		
		ob_start();
		include(ViewCompiler::getView($this->path, $contoller, $function, $this->html));
		$contents = ob_get_contents();
		ob_end_clean();
		
		return $contents;
		
	}
	
	public static function renderView($path, $vars) {
		$v = new View($path);
		return $v->render($vars);
	}
	
}

?>
