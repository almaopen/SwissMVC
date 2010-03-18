<?php

/**
 * The abstract Datasource -class defines methods for accessing data in a database. There's no big abstraction,
 * but just a wrapper for easy swapping of one database to another. The Datasource -class does provide
 * for some utility methods, such as automatic SQL query logging.
 */
abstract class Datasource {
	
	private $queryLog = array();
	
	private static $_self = null;
	
	/**
	 * Escape data using the datasources own escape method
	 */
	public static function escape($data) {
		
		if(Datasource::$_self == null) {
			Datasource::$_self = Datasource::_create();
		}
		
		return Datasource::$_self->_escape($data);
		
	}
	
	/**
	 * Make a query into the datasource
	 */
	public static function query($sql) {
		
		if(Datasource::$_self == null) {
			Datasource::$_self = Datasource::_create();
		}
		
		Datasource::$_self->_logQuery($sql);
		
		try {
			return Datasource::$_self->_query($sql);
		} catch(Exception $e) {
			throw $e;
		}
			
	}
	
	/**
	 * Logs this query
	 */
	protected final function _logQuery($sql) {
		$this->queryLog = array("time" => time(), "query" => $sql);
	}
	
	/**
	 * Create an instance of the datasource provider
	 */
	private static function _create() {
		
		/**
		 * The name of the class is always <Implementation>Datasource where Implementation
		 * is the camelcase name of the database
		 */
		$implClass = strtoupper(substr(AppConfiguration::$DATASOURCE['db.engine'], 0, 1)) . 
			strtolower(substr(AppConfiguration::$DATASOURCE['db.engine'], 1)) . "Datasource";
			
		/**
		 * If the class is not in memory, require it from the /libs/datasource/ -directory
		 */
		if(!class_exists($implClass)) {
			if(file_exists(dirname(__FILE__) . "/" . AppConfiguration::$DATASOURCE['db.engine'] . "_datasource.php"))
				require_once(dirname(__FILE__) . "/" . AppConfiguration::$DATASOURCE['db.engine'] . "_datasource.php");
			else
				throw new Exception("Datasource provider " . AppConfiguration::$DATASOURCE['db.engine'] . " not found");
		}
		$impl = new $implClass();
		$impl->_connect(AppConfiguration::$DATASOURCE);
		return $impl;
		
	}
	
	/**
	 * Implement a __sleep -method to write SQL queries to a log file (if we want to
	 * do that) and close active connections
	 */
	public function __sleep() {
		
		// Close active connections
		$this->_disconnect();
		
		// See if we log the SQL queries to file
		
		// If the property AppConfiguration::$SQL_DEBUG is set, then we see
		// what to do with the SQL query log
		if(property_exists('AppConfiguration', 'SQL_DEBUG')) {
			if(AppConfiguration::$SQL_DEBUG['log_queries']) {
				
				$queryLog = "";
				foreach($this->queryLog as $item) {
					$queryLog .= "[" . date("Y-m-d H:i", $item["time"]) . "] " . $item["query"] . "\n";
				}
				
				if(AppConfiguration::$SQL_DEBUG['log_target'] == "print") {
					// Just dump it
					?>
					<div id="mvcQueryLog">
					 <pre>
					  <?=$queryLog?>
					 </pre>
					</div>
					<?
				} else {
					// Presume we're logging to a file
					file_put_contents($queryLog, AppConfiguration::$SQL_DEBUG['log_target'], FILE_APPEND | LOCK_EX);
				}
				
			}
		}		
	}
	
	/**
	 * Abstract methods for the subclasses to implement 
	 */

	/**
	 * Connect to the datasource. The configuration is provided from AppConfiguration
	 * and should contain the username, password, host and database name that
	 * the connection should be made to
	 */
	 public abstract function _connect($configuration);
	 
	 /**
	  * Disconnect from the datasource if a connection exists. This method should not
	  * throw any exceptions as it's called from the __sleep -method
	  */
	 public abstract function _disconnect();
	 
	 /**
	  * Escape any data to SQL-safe
	  */
	 public abstract function _escape($string);
	 
	 /**
	  * Execute a query to the database. On select this method should return the rows
	  * in associative arrays. On insert, it should return the new row ID (if one exists)
	  * and on update/delete it should return true if the query succeeds.
	  */
	 public abstract function _query($sql);

}

?>
