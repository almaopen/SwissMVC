<?php

define("WEBROOT", dirname(__FILE__));
define("WEBAPP_ROOT", dirname(__FILE__) . "/../webapp/");
define("LIBS_ROOT", dirname(__FILE__) . "/../libs/");

require_once(LIBS_ROOT . "/mvc.php");

// Execute the action 
MVC::parseRequest();

?>
