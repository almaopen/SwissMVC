<?php

class ViewCompiler {
	
	/* Elements than can be closed with />, others with ></tag> */
	public static $SIMPLE_ELEMENTS = array("hr","br","img","link","meta", "input");
	
	public $controller = null;
	public $method = null;
	public $viewPath = null;
	public $html = null;
	
	private function ViewCompiler($v, $c, $f) {
		$this->viewPath = $v;
		$this->controller = $c;
		$this->method = $f;
		$this->html = new HtmlHelper($c);
	}
	
	private function traverse($node) {
				
		if(preg_match("#^mvc:#", $node->nodeName)) {

			$nodeName = str_replace('mvc:', '', $node->tagName);
			
			$handler_script = $nodeName . "_handler";
			$handler_class = Inflector::camelize($handler_script);
			if(!class_exists($handler_class)) {
				require_once(dirname(__FILE__) . "/components/$handler_script.php");
			}
			$control = new $handler_class();
			$control->html = $this->html;
			
			if($control->start_control($this, $node)) {
				/* Iterate child-nodes */
				foreach($node->childNodes as $child) {
					if($child->nodeType == XML_ELEMENT_NODE) {
						$this->traverse($child);
					} else {
						echo (string)$child->nodeValue;
					}
				}
			}
			
			$control->end_control();

		} else {
			// Create normal HTML -element
			$elemBase = "<%s" . ($node->hasAttributes() ? " %s" : "") . ($node->hasChildNodes() ? ">" : 
				(in_array($node->tagName, ViewCompiler::$SIMPLE_ELEMENTS) ? "/>" : "></%s>"));
			$attributes = array();
			foreach($node->attributes as $attribute) {
				$attributes[] = $attribute->name . "=\"" . $attribute->value . "\"";
			}
			
			if($node->hasChildNodes()) {
				printf($elemBase, $node->tagName, join($attributes, " "));
				
				foreach($node->childNodes as $child) {
					if($child->nodeType == XML_ELEMENT_NODE) {
						$this->traverse($child);
					} else {
						echo (string)$child->nodeValue;
					}
				}
				
				echo "</" . $node->tagName . ">";
			} else {
				
				if(in_array($node->tagName, ViewCompiler::$SIMPLE_ELEMENTS)) {
					printf($elemBase, $node->tagName, join($attributes, " "));
				} else {
					printf($elemBase, $node->tagName, join($attributes, " "), $node->tagName);					
				}
				
			}
			
		}

		
	}
	
	
	public static function compileView($path, $file, $controller, $function) {
		
		/* Set an error handler to trap compilation errors */
		set_error_handler("ViewCompiler::errorHandler", E_WARNING);
		
		/* Wrap the file in <mvc:components> to make sure it's XML */
		$file = "<mvc:components xmlns:mvc=\"http://simplemvc.org/ns/2010\">$file\n</mvc:components>";
		
		/* 
		 * We need to do some modifications to the file so it's processed properly, for example
		 * converting HTML -comments to something else (<!-- -->) as otherwise CSS or JavaScript
		 * might be left out. We convert them back after the file has been compiled
		 */
		$file = ViewCompiler::prepareFileForCompilation($file);
		
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = true;
		$dom->loadXML($file);
		
		// Traverse the tree
		$compiler = new ViewCompiler($path, $controller, $function);
		
		ob_start();
		$compiler->traverse($dom->documentElement);
		$compiledView = ob_get_contents();
		ob_end_clean();
		
		return ViewCompiler::cleanFileAfterCompilation($compiledView);		
		
	}
	
	public static function getView($path, $contoller, $function) {
		
		$cacheFile = WEBAPP_ROOT . "/tmp/viewcache/" . md5($path);

		if(file_exists($cacheFile) && false) {
			if(filemtime($cacheFile) > filemtime($path)) {
				return $cacheFile;
			}
		}
		
		$viewFile = file_get_contents($path);
		
		/* If there's a <mvc:> -tag in the view file, process it via the compiler */
		if(strpos($viewFile, "<mvc:") !== false) {
			$viewFile = ViewCompiler::compileView($path, $viewFile, $contoller, $function);	
		}
		
		/* Cache the view */
		file_put_contents($cacheFile, $viewFile);
		
		return $cacheFile;
		
	}
	
	/*
	 * Utility functions for handlers
	 */
	 
	 public static function attributesAsArray($nodelist) {
		$attribs = array();
		for($i = 0; $i < $nodelist->length; $i++) {
			$attribs[$nodelist->item($i)->name] = $nodelist->item($i)->value;
		}	 	
		return $attribs;
	 }
	 
	 private static function dumpAttribs($node) {
	 	foreach($node->attributes as $attribute) {
					echo $attribute->name . "=\"" . $attribute->value . "\" ";
		}
	 }
	 
	/**
	 * Generates a HTML-fragment from the children of the node.
	 */
	public static function collect($elem) {
		
		$string = "";
		
		foreach($elem->childNodes as $node) {

			if($node->nodeType == XML_ELEMENT_NODE) {
				
				// Create normal HTML -element
				$elemBase = "<%s" . ($node->hasAttributes() ? " %s" : "") . ($node->hasChildNodes() ? ">" : "/>");
				$attributes = array();
				foreach($node->attributes as $attribute) {
					$attributes[] = $attribute->name . "=\"" . $attribute->value . "\"";
				}
				
				if($node->hasChildNodes()) {
					
					$string .= sprintf($elemBase, $node->tagName, join($attributes, " "));
					$string .= ViewCompiler::collect($node);
					$string .= "</" . $node->tagName . ">";
					
				} else {
					$string .= sprintf($elemBase, $node->tagName, join($attributes, " "));
				}
				
			} else {
				$string .= (string)$node->nodeValue;
			}
		}
		
		return $string;
		
	}
	
	private static $prep_rules = array(
			"#<!--#" => "MVC_HTML_COMMENT_START",
			"#-->#" => "MVC_HTML_COMMENT_END",
			"#<\?(php)?#" => "MVC_PHP_CODE_START",
			"#\?>#" => "MVC_PHP_CODE_END",
			"#< #" => "MVC_OPEN_TAG",
			"#&#" => "MVC_ENT_START"
		);
	
	private static $clean_rules = array(
			"#MVC_HTML_COMMENT_START#" => "<!--",
			"#MVC_HTML_COMMENT_END#" => "-->",
			"#MVC_PHP_CODE_START#" => "<?",
			"#MVC_PHP_CODE_END#" => "?>",
			"#MVC_OPEN_TAG#" => "< ",
			"#MVC_ENT_START#" => "&"
		);
	
	
	private static function prepareFileForCompilation($file) {
		foreach(ViewCompiler::$prep_rules as $regex => $replace) {
			$file = preg_replace($regex, $replace, $file);
		}
		return $file;
	}
	
	public static function cleanFileAfterCompilation($file) {
		foreach(ViewCompiler::$clean_rules as $regex => $replace) {
			$file = preg_replace($regex, $replace, $file);
		}
		return $file;		
	}
	
	public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		if(strpos("DOMDocument::loadXML()", $errstr) == 0) {
			// Parse error
			list($prefix, $error) = explode("]: ", $errstr);
			SwissMVCErrors::generalError("Could not compile view " . realpath($errcontext["path"]) . ": " . $error, false);
		}
		return false;
	}
	
}

?>
