<?php

/**
 * The options-class provides easy handling of user options. Each Option-set needs to be in a "realm",
 * for example "user"-realm for user options, "blog"-realm for the options of a blog.
 * 
 * The options-class can also use default values for options if none are defined for some of the
 * options in a set. To do this, the application configuration needs to have the configuration "options" set:
 * 
 * public static $OPTIONS = array(
 * 	"user" => 
 * 			array(
 * 				array("name" => "USER_SHOW_NOTIFICATIONS", "type" => "boolean", "default" => "false"),
 * 				array("name" => "USER_SHOW_EMAIL", "type" => "boolean", "default" => "false")
 * 			)
 * );
 */
class Options {
	
	/**
	 * The realm that this set represents
	 */
	private $_optionRealm = null;
	
	/**
	 * The realm configuration
	 */
	private $_realmConfig = array();
	
	/**
	 * Our parent session that stores us
	 */
	private $_sessionParent	= null;
	
	/**
	 * The realm values
	 */
	private $_realmOptions = array();	 
	
	/**
	 * The ID that associates this option-set to the datastorage layer
	 */
	private $_internalID;
	/**
	 * The dataset name in the database
	 */	 
	private $_datasetName;
	
	/**
	 * Creates a new Options -set
	 */
	public function Options($realm, $values = array()) {
		$this->_optionRealm = $realm;
		if(!class_exists("AppConfiguration")) {
			SimpleMVCErrors::generalError("Application configuration class not available but is required by Options-class. " . 
					"Make sure that webapp/application.conf.php is present");
		}
		if(!is_array(AppConfiguration::$OPTIONS[$realm])) {
			SimpleMVCErrors::generalError("Requesting options for non-existant Options-realm: $realm. Make sure " .
				"webapp/application.conf.php contains \$OPTIONS[\"$realm\"].");
		}
		$this->_realmConfig = AppConfiguration::$OPTIONS[$realm];
		// if(!empty($values)) {
			$this->_initializeWithDefaults();
			foreach($values as $key => $value) {
				$this->_realmOptions[$key] = $value;				
			}
		// }
	}
	
	/**
	 * Looks up a option value
	 */
	public function lookupOption($optionKey) {
		return $this->_realmOptions[$optionKey];
	}
	
	/**
	 * Sets a new option value. Also triggers store on the options
	 */
	public function setOptionValue($optionKey, $optionValue) {
		$this->_realmOptions[$optionKey] = $optionValue;
		$this->_serializeOption($optionKey, $optionValue);
		if(!empty($this->_sessionParent)) {
			$this->_sessionParent->invalidateOptions($this->_realmName);
		}
	}
		
	/**
	 * Updates the options from the input
	 */
	public function saveFromInput($input) {
		foreach(array_keys($this->_realmOptions) as $key) {
			if(!empty($input[$key]) || $input[$key] == 0) {
				$this->setOptionValue($key, $this->_getOptionValue($input[$key], $this->_realmConfig[$key]["type"]));
			}
		}
	}
	
	public function _initializeWithDefaults() {
		$this->_realmOptions = array();
		foreach(array_keys($this->_realmConfig) as $optionKey) {
			$this->_realmOptions[$optionKey] = $this->_realmConfig[$optionKey]["default"];
		}				
	}
	
	/**
	 * Loads the options from the DB
	 */
	public function _loadOptions() {
		
		$this->_realmOptions = array();
		
		$rows = Datasource::query("select * from " . $this->_optionRealm . "_options where id=" . $this->_internalID);
		// First build up from defaults
		foreach(array_keys($this->_realmConfig) as $optionKey) {
			$this->_realmOptions[$optionKey] = $this->_realmConfig[$optionKey]["default"];
		}
		foreach($rows as $row) {
			$this->_realmOptions[$row["optionname"]] = ($this->_getOptionValue($row["optionvalue"],
																			$this->realmConfig[$row["optionname"]]["type"]));
		}
		
	}
	
	public function _setInternalID($id) {
		$this->_internalID = $id;
	}	 
	
	/**
	 * Serialize a certain option key-value pair (meaning it has been updated)
	 */
	public function _serializeOption($optionKey, $optionValue) {
		// First sanitize $optionValue
		if($this->_realmConfig[$optionKey]["type"] == "int") {
			if(!is_numeric($optionValue)) {
				throw "Bad option data. " . $this->_optionRealm . ".$optionKey expects numeric value, $optionValue given!";
			}
		} else if($this->_realmConfig[$optionKey]["type"] == "boolean") {
			$optionValue = ($optionValue) ? '1' : '0';			
		} else {
			$optionValue = "'" . DataSource::escape($optionValue) . "'";
		}
		if(!defined("OPTIONS_SUPPRESS_DELETE")) {
			DataSource::query("delete from " . $this->_optionRealm . "_options where id=" . $this->_internalID . " and " .
					"optionname='" . $optionKey . "'");
		}
		DataSource::query("insert into " . $this->_optionRealm . "_options values (" . $this->_internalID . "," .
				"'$optionKey',$optionValue)");
	}
	
	/**
	 * Associates the parent session. Should never be called from actions, framework internal!
	 */
	public function _setSessionParent($sess) {
		$this->_sessionParent = $sess;
	}
	
	public function _getOptionValue($value, $type) {                                                                                                            
		if($type == "int") {                                                                                                                                
			return intval($value);                                                                                                                            
		} else if($type == "boolean") {                                                                                                                     
			if(strtolower($value) == "true" || $value == 1) {                                                                                                                
				return true;                                                                                                                                    
			} else {                                                                                                                                          
				return false;                                                                                                                                   
			}                                                                                                                                                 
		} else {                                                                                                                                            
			return $value;                                                                                                                                    
		}                                                                                                                                                   
	}      
	
}

?>
