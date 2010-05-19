<?

/**
 * Representation of a controller
 */
class Controller {
	
	/**
	 * The context variables that will then be available to the view
	 */
	public $_contextVariables = array();
	
	/**
	 * The template that will be used. Note: this is different from the view
	 */
	public $template = "default";
	
	/**
	 * Flag to display whether or not rendering should be done.
	 * For example, redirect() sets this to false
	 */
	public $_renderView = true;
	
	/**
	 * The input for this controller
	 */
	public $input = array();	 
	 
	/**
	 * The Session for this view
	 */
	public $session = null;
	
	/**
	 * Controller parameters
	 */
	public $params = array();
	
	/**
	 * Request URI
	 */
	public $_requestURI = null;
	
	/**
	 * The model that's automatically bound to this controller
	 * 
	 * Automatic model binding is done by name. If we have an action named "FoobarController", then
	 * we automatically try to bind a model named "Foobar" to it.
	 */
	private $autoBindModel = null;
	
	/**
	 * The name of the controller
	 */
	private $controllerName = null;
	
	/**
	 * API response
	 */
	private $apiResponse = null;
	
	var $view = null;
	
	/**
	 * Starts the new action
	 */
	public function Controller() {
		// Restore the session (or create a new one if this is the first time)
		$this->session = Session::restoreSession();
	}
	
	public function _init() {
		
		/*
		 * Auto bind the model
		 * 
		 * If the controller -class has defined the variable "bindModelName", then we use the name in that variable,
		 * otherwise use the name of controller
		 * 
		 * Example:
		 * 
		 * class FoobarController extends Controller {
		 * 		var $bindModelName = "users";
		 * }
		 * 
		 * This would cause the controller to automatically bind to model "users" instead of "foobar"
		 * 
		 */
		$this->autoBindModel = Model::getModelIfExists(empty($this->bindModelName) ? $this->controllerName : $this->bindModelName);

		/*
		 * Add the AUTOLOAD -models to this Controller. Autoload -models are models
		 * which are always available via $this->Modelname in all controllers
		 */
		if(property_exists('AppConfiguration', 'AUTOLOAD_MODELS')) {
			foreach(AppConfiguration::$AUTOLOAD_MODELS as $model) {
				$casedName = Inflector::camelize($model);
				$this->$casedName = Model::getModel($model);
			}
		}

	}
	
	/**
	 * Model functions to easy access of automatically bound model
	 */
	public function load($conditions = array(), $deep = false) {
		return $this->autoBindModel->load($conditions, $deep);
	}
	
	public function loadAll($conditions = array(), $deep = false) {
		return $this->autoBindModel->loadAll($conditions, $deep);
	}
		 
	
	/**
	 * Set a variable in the context of this controller. These will then
	 * be made available to the view with extract()
	 * 
	 * @param $key Key to access the variable
	 * @param $variable The variable
	 */
	public function set($key, $variable) {
		$this->_contextVariables[$key] = $variable;
	}
	
	public final function redirect($target) {
		
		if(defined("MVC_DISABLE_REDIRECTS")) {
			/* Disabled by API */
			return;
		}
		
		header("Location: $target");
		$this->_renderView = false;
	}
	
	public function setAPIResponse($data) {
		$this->apiResponse = $data;
	}
	
	public function getAPIResponse() {
		return $this->apiResponse;
	}
	
	public final function __sleep() {
		Datasource::disconnect();
	}
	
	
}
	
	

?>
