<?php

/**
 * The ControllerGenerator is used by the MVC control handlers to automatically generate server-side implementations
 * from view code
 */
class ControllerGenerator {
	
	public static $generatedControllers = array();
	
	/**
	 * Generates code to save a certain model
	 */
	public static function generateSaveController($controller, $action, $model, $target) {
		
		$controllerHash = md5($controller . "/" . $action);

		if(!isset(ControllerGenerator::$generatedControllers[$controllerHash])) {
			ControllerGenerator::$generatedControllers[$controllerHash] = 0;
		}
		
		ControllerGenerator::$generatedControllers[$controllerHash]++;
		
		$controllerFile = WEBAPP_ROOT . "/tmp/controllers/Temp${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . ".php";		
		
		$skeleton = file_get_contents(dirname(__FILE__) . "/controller-skeletons/save-skeleton.phps");
		
		$data = array(
			"{name}" => "Temp${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash],		
			"{method}" => "func",
			"{model}" => $model,
			"{target}" => $target,
			"{source}" => "/$controller/$action",
		);

		$controllerCode = str_replace(array_keys($data), array_values($data), $skeleton);
		file_put_contents($controllerFile, $controllerCode);
		
		ControllerGenerator::addRoute
			(
				"/${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . "/func", 
				$controllerFile, 
				"Temp${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . "Controller"
			);
		
		
		return "/${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . "/func";
		
	}
	
	public static function generateBasicController($controller, $action, $code) {
		
		$controllerHash = md5($controller . "/" . $action);
		
		if(!isset(ControllerGenerator::$generatedControllers[$controllerHash])) {
			ControllerGenerator::$generatedControllers[$controllerHash] = 0;
		}
		
		ControllerGenerator::$generatedControllers[$controllerHash]++;

		$controllerFile = WEBAPP_ROOT . "/tmp/controllers/Temp${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . ".php";
		
		$skeleton = file_get_contents(dirname(__FILE__) . "/controller-skeletons/basic-skeleton.phps");
	
		// Fill in the blanks
		$data = array(
			"{name}" => "Temp${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash],
			"{method}" => "func",
			"{code}" => ViewCompiler::cleanFileAfterCompilation($code)
		);
		$controllerCode = str_replace(array_keys($data), array_values($data), $skeleton);
		file_put_contents($controllerFile, $controllerCode);
		
		ControllerGenerator::addRoute
			(
				"/${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . "/func", 
				$controllerFile, 
				"Temp${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . "Controller"
			);
		
		if(property_exists("AppConfiguration", "APP_PATH")) {
			$path_prefix = AppConfiguration::$APP_PATH;
		} else {
			$path_prefix = "";
		}
		
		return "${path_prefix}/${controllerHash}_" . ControllerGenerator::$generatedControllers[$controllerHash] . "/func";		

	}
	
	private static function addRoute($requestPath, $targetFile, $className) {
		
		// Update routing		
		$currentRoutes = array();
		if(class_exists("AutoRoutes")) {
			$currentRoutes = AutoRoutes::$AUTO_ROUTES;
		}
		
		$currentRoutes[$requestPath] = array("file" => $targetFile, "class" => $className);
		
		ControllerGenerator::writeRoutes($currentRoutes);
		
	}
	
	public static function writeRoutes($routes) {
		
		$skeleton = file_get_contents(dirname(__FILE__) . "/autoroute_base.phps");
		$routefile = str_replace("/*ROUTES*/", "unserialize('" . serialize($routes) . "');", $skeleton);
		file_put_contents(WEBAPP_ROOT . "/tmp/autoroutes.php", $routefile);
		AutoRoutes::$AUTO_ROUTES = $routes;
		
	}
	
}

?>
