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
	
	/**
	 * The controller
	 */
	private $contoller = null;
	
	private $contents = null;
	
	/**
	 * The channel we're rendering in
	 */
	private $channel = null;
	
	public function View($viewPath, $channel = null) {
		$this->path = $viewPath;
		$this->channel = $channel;
		$this->session = Session::restoreSession();
	}
	
	public function render($vars, $controller, $function) {
		
		$this->controller = $controller;
		
		$this->html = new HtmlHelper(MVCContext::getContext()->getController());
		
		extract($vars);
		
		ob_start();
		include(ViewCompiler::getView($this->path, $controller, $function, $this->html));
		$contents = ob_get_contents();
		ob_end_clean();
		
		$this->contents = $contents;
		
	}
	
	public function common($file) {

		$path = WEBAPP_ROOT . "/views/" . (empty($this->channel) ? "" : $this->channel . "/") . "common/$file.php";
		if(!file_exists($path)) {
			SwissMVCErrors::generalError("Cannot find view include $path", false);
		}
		
		include($path);
		
	}
	
	public function showTemplate($file) {
		$content_for_template = $this->contents;
		if(isset($this->controller->_contextVariables["title_for_page"])) {
			$title_for_page = $this->controller->_contextVariables["title_for_page"];
		}
		
		
		include($file);
	}
	
	public static function renderView($path, $vars) {
		$v = new View($path);
		return $v->render($vars);
	}
	

}

?>
