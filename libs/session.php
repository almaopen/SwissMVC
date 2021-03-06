<?php

/**
 * Session class to hold internal session. The Sessin object is stored inside the PHP session
 * $_SESSION should not be used directly from apps.
 * 
 */
class Session {
	
	/**
	 * The session variables
	 */
	private $_sessionVariables = array();
	
	/**
	 * Authentication status
	 */
	private $_authenticated = array();
	
	/**
	 * Permissions set for this user.
	 * Permissions work so that either the user has it, or doesn't
	 * 
	 * There can be multiple different types of permissions, for example "user"-permissions,
	 * "blog"-permissions, etc.
	 */
	private $_permissionRealms = array();
	
	/**
	 * Options for this session
	 */
	private $_options = array();
	private $_optionRealmKeys = array();
	
	/**
	 * Flash message
	 */
	private $_flashMessage = null;

	
	/**
	 * Set the permissions for this user
	 */
	public function addPermission($realm, $permission) {
		if(!is_array($this->_permissionRealms[$realm])) {
			$this->_permissionRealms[$realm] = array();
		}
		$this->_permissionRealms[$realm] = $permission;
	}

	/**
	 * Sets a flashmessage
	 */
	public function setFlash($fl) {
		$this->_flashMessage = $fl;
	}	 
	
	public function destroy() {
		$his->_flashMessage = null;
		$this->_sessionVariables = array();
		$this->_authenticated = false;
		$this->_permissionsRealm = array();
		$this->_options = array();
		$this->_optionRealmKeys = array();
		$this->_storeToInternalSession();
	}
		
	public function hasFlash() {
		return ($this->_flashMessage != null);
	}
	
	public function getFlash() {
		$msg = $this->_flashMessage;
		$this->_flashMessage = null;
		return $msg;
	}
	
	/**
	 * Get the options for a certain realm with the certain key
	 */
	public function getOptions($realm, $key) {
		$needsLoading = true;
		if($this->_options[$realm] != null) {
			if($this->_optionRealmKeys[$realm] != $key) {
				$needsLoading = true;
			}
		} else {
			$needsLoading = true;
		}
		if($needsLoading) {
			$options = new Options($realm);
			$options->_setInternalID($key);
			$options->_loadOptions();
			$this->_optionRealmKeys[$realm] = $key;
			$this->_options[$realm] = $options;
			$options->_setSessionParent($this);
		}
		return $this->_options[$realm];
	}
	
	public function invalidateOptions($realm) {
		unset($this->_options[$realm]);
	}
	
	/**
	 * Remove a certain permission
	 */
	public function removePermission($realm, $permission) {
		unset($this->permissionRealms[$realm][$permission]);
	}
	
	/**
	 * Check a certain permission
	 */
	public function hasPermission($realm, $permission) {
		return (isset($this->_permissionRealms[$realm]));
	}
	
	/**
	 * Set the authentication status for this user in this application.
	 * For example, when a user logs in, the action can set $this->Session->authenticateToApplication(true)
	 * so other actions can easily then query: $this->Session->isAuthenticated();
	 */
	public function authenticateToApplication($flag, $realm = "_DEFAULT_REALM_") {
		$this->_authenticated[$realm] = $flag;
	}
	
	/**
	 * Query the authentication status of this user
	 */
	public function isAuthenticated($realm = "_DEFAULT_REALM_") {
		return $this->_authenticated[$realm];
	}
	
	/**
	 * Sets the given key. If the third parameter is false, then the 
	 * key is not replaced if it's already found
	 */
	public function set($key, $value, $replace = true) {
		
		if($this->_sessionVariables[$key] != null && !$replace)
			return;
		
		if(is_object($value)) {
			$classname = get_class($value);
			if(preg_match("#Model\$#", $classname)) {
				$this->_storedClasses[$classname] = true;
				$this->set("__SwissMVC_stored_classes", $this->_storedClasses);
			}
		}
			
		$this->_sessionVariables[$key] = $value;
		$this->_storeToInternalSession();
		
	}
	
	/**
	 * Gets the given key. If not found, return default (which be default is null)
	 */
	public function get($key, $default = null) {
		if($this->_sessionVariables[$key] != null) {
			return $this->_sessionVariables[$key];
		}
		return $default;
	}
	
	/**
	 * Restores the session
	 */
	public static function restoreSession() {
		session_start();
		if($_SESSION['simplemvcsession'] != null) {
			$session = $_SESSION['simplemvcsession'];
			return $session;
		} else {
			$session = new Session();
			$_SESSION['simplemvcsession'] = $session;
			return $session;
		}
	}
	
	/**
	 * Stores this session inside the $_SESSION-variable
	 */
	public function _storeToInternalSession() {
		$_SESSION['simplemvcsession'] = $this;
	}
	
		
}

?>
