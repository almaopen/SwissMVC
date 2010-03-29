<?php

// M
require_once(LIBS_ROOT . "/model/model.php");
require_once(LIBS_ROOT . "/model/modelcache.php");
require_once(LIBS_ROOT . "/model/validators.php");
require_once(LIBS_ROOT . "/model/listener.php");

// V
require_once(LIBS_ROOT . "/view/view.php");
require_once(LIBS_ROOT . "/view/compiler.php");
require_once(LIBS_ROOT . "/view/controlhandler.php");
require_once(LIBS_ROOT . "/view/controller-generator.php");

// C
require_once(LIBS_ROOT . "/controller.php");
require_once(LIBS_ROOT . "/filter.php");


require_once(LIBS_ROOT . "/datasource/datasource.php");

require_once(LIBS_ROOT . "/utils/inflector.php");
require_once(LIBS_ROOT . "/utils/controller_errors.php");
require_once(LIBS_ROOT . "/utils/errors.php");
require_once(LIBS_ROOT . "/utils/mailutil.php");

require_once(LIBS_ROOT . "/options.php");
require_once(LIBS_ROOT . "/session.php");

require_once(LIBS_ROOT . "/helpers/htmlhelper.php");

define("INPUT_METHOD_POST", "POST");
define("INPUT_METHOD_GET", "GET");

/**
 * Include the application configuration
 */
require_once(WEBAPP_ROOT . "/application.conf.php");

class MVC {
	
	
	public static final function parseRequest() {
		
		/*
		 * Parse the request
		 */		
		 
		// Parse query string
		if(strpos($_SERVER['QUERY_STRING'], "?") !== false) {
			list($requestURI, $params) = explode("?", $_GET['url']);
			$params = substr($_SERVER['QUERY_STRING'], strpos($_SERVER['QUERY_STRING'], "?") + 1);
		} else {
			$requestURI = $_GET['url'];
		}
		
		// Parse controller parameters
		$controllerParams = array();
		if(strpos($requestURI, ";") !== false) {
			list($requestURI, $paramSegment) = explode(";", $requestURI);
			$paramParts = explode("/", $paramSegment);
			foreach($paramParts as $part) {
				list($key, $value) = explode(":", $part);
				$controllerParams[$key] = $value;
			}
		}

		// Parse the URL
		$urlParts = explode("/", $requestURI);
		$controllerScript = $urlParts[0];
		if(empty($controllerScript)) {
			$controllerScript = "index";
		}
		
		// Function is $urlParts[1]
		$controllerFunction = ($urlParts[1] == null ? "index" : $urlParts[1]);
		
		MVC::executeController
			(
				$controllerScript,
				$controllerFunction,
				$controllerParams,
				$requestURI,
				array_slice($urlParts, 2),
				$params
			);
		
	}	

	public static final function executeController($controllerScript, 
													$controllerFunction, 
													$controllerParams,
													$requestURI,
													$methodParameters,
													$queryString,
													$presetVariables = array()) {
														
		// Check routing
		$routes = array();
		if(property_exists('AppConfiguration', 'ROUTES')) {
			$routes = AppConfiguration::$ROUTES;
		}
		
		if(file_exists(WEBAPP_ROOT . "/tmp/autoroutes.php")) {
			require_once(WEBAPP_ROOT . "/tmp/autoroutes.php");
			$routes = array_merge($routes, AutoRoutes::$AUTO_ROUTES);			
		}
		
		/**
		 * Check if the request is mapped normally or if we have a routing rule for it
		 */
		if(!empty($routes["/$controllerScript/$controllerFunction"])) {
			/* Use routing */
			$route = $routes["/$controllerScript/$controllerFunction"];
			$controllerFilename = $route["file"];
			$controllerClass = $route["class"];
		} else {
			/* Normal mapping */
			// Find the controller class
			$controllerFilename = WEBAPP_ROOT . "/controllers/" . $controllerScript . "_controller.php";
			$controllerClass = ucfirst($controllerScript) . "Controller";
		}

		
		if(!file_exists($controllerFilename)) {
			controllerErrors::missingcontroller($controllerScript);			
		}
		
		require_once($controllerFilename);
		
		/*
		 * Populate some variable to the controller
		 */		
		$controller = new $controllerClass();
		$controller->_requestURI = $requestURI;
		$controller->params = $controllerParams;
		$controller->controllerName = strtolower($controllerScript);

		if(!empty($presetVariables)) {
			$controller->_contextVariables = $presetVariables;
		}		

		// Initialize the controller
		$controller->_init();

		
		/*
		 * See if the requested method exists. If not, we try to find
		 * a method with the name _default to call, which is a "catch-all" -method
		 */
		if(!method_exists($controller, $controllerFunction)) {
			if(!method_exists($controller, "_default")) {
				controllerErrors::missingMethod($controllerScript, $controllerFunction);
			} else {
				$methodParameters = array_merge(array($controllerScript, "default", $controllerFunction), array_slice($methodParameters, 3));
				$controllerFunction = "_default";
			}
		}
		
		/*
		 * Populate input
		 */
		 
		$input = array();
		 
		if(!empty($_POST)) {
			$input = $_POST;
			$controller->inputmethod = INPUT_METHOD_POST;
		} else {
			parse_str($queryString, $input);
			$controller->inputmethod = INPUT_METHOD_GET;			
		}		
		
		/*
		 * Begin the execution by processing any filters
		 */
		if(property_exists('AppConfiguration', 'FILTERS')) {
			
			foreach(AppConfiguration::$FILTERS as $filter) {
				if($filter["target"][0] == $controllerScript ||
					$filter["target"][0] == "*") {
					// Controller -part matches, see if the action matches	
					if($filter["target"][1] == $controllerFunction ||
						$filter["target"][1] == "*" ||
						(is_array($filter["target"][1]) && in_array($controllerFunction, $filter["target"][1]))) {
							// Matches, execute the filter
						if(!class_exists($filter["filter"])) {
							require_once(WEBAPP_ROOT . "/filters/" . Inflector::decamelize($filter["filter"]) . ".php");
						}
						/*
						 * Invoke the filter. We provide as parameter the controller name, action name and the input
						 * sent from the browser. The input is passed by reference so it can be modified
						 * by the filter along with any parameters defined in the AppConfiguration
						 */
						$filterClass = $filter["filter"];
						$filterImpl = new $filterClass();
						if(call_user_func_array(
								array($filterImpl, "processFilter"),
								array($controllerScript, $controllerFunction, $filter["parameters"], &$input) 
								) === false) {
						  // By returning false, the filter can stop the processing
						  // Then we check if we just "die" or do we do some rendering
						  if($filterImpl->_renderView) {
						  	MVC::renderView(
						  		!empty($filterImpl->view) ? $filterImpl->view : $controllerFunction,
						  		$filterImpl->template,
						  		$filterImpl->_contextVariables,
						  		$controllerScript,
						  		$controllerFunction
						  	);
						  }
						  // Exit
						  exit();
						}
					}
				}				
			}
			
		}
		
		/*
		 * Populate the controller with the input, now that it's been through the filters
		 */
		$controller->input = $input;
		
		/*
		 * Then continue to the controller
		 */
		call_user_func_array(array($controller, $controllerFunction), $methodParameters);

		/*
		 * Render the view
		 */
		if($controller->_renderView) {
			
			/*
			 * Render the view
			 */
			MVC::renderView
				(
					empty($controller->view) ? $controllerFunction : $controller->view,
					$controller->template,
					$controller->_contextVariables,
					$controllerScript,
					$controllerFunction		
				);
			
			
		}
		
	} 
	
	public static function renderView($viewFile, $template, $variables, $script, $function) {
		
		/*
		 * See which channel we should be using. If the configuration file contains the AUTO_CHANNEL_SELECTION
		 * configuration, we set the channel accordingly using the host name. 
		 */			
		$channel = "web";
		if(property_exists('AppConfiguration', 'AUTO_CHANNEL_SELETION')) {
			if(AppConfiguration::$AUTO_CHANNEL_SELECTION[$_SERVER['HTTP_HOST']]) {
				$channel = AppConfiguration::$AUTO_CHANNEL_SELECTION[$_SERVER['HTTP_HOST']];
			}
		}
		
		$view = new View(WEBAPP_ROOT . "/views/$channel/$script/$viewFile.php");

		if(!file_exists($view->path)) {
			ControllerErrors::missingView($script, $function, "WEBAPP_ROOT/views/$channel/" . $script . "/" .
												$viewFile . ".php");
		}		
		
		/*
		 * Render the view using the variable set by the controller
		 */
		$content_for_template = $view->render($variables, $script, $function);
		
		/*
		 * Get the template that we're using
		 */
		if(empty($template)) {
			$template = "default";
		}
		
		$layoutFile = WEBAPP_ROOT . "/templates/" . $template . ".phtml";
		if(!file_exists($layoutFile)) {
			controllerErrors::missingTemplate("webapp/templates/" . $template . ".phtml");
		}
		
		/**
		 * See if we have a page title
		 */
		if(isset($variables["title_for_page"])) {
			$title_for_page = $variables["title_for_page"];
		}
		
		/*
		 * Include the layoutfile
		 */
		require_once($layoutFile);
		
		
	}
	
	
}

?>
