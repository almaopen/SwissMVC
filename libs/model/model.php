<?php

class Model {
	
	/*====================================*
	 * STATIC STUFF
	 *====================================*/
	
	/**
	 * Static function to get a copy of this model
	 */
	public static function getModel($name) {

		$modelClass = strtoupper(substr($name, 0, 1)) . substr($name, 1) . "Model";
		if(!class_exists($modelClass)) {
			// See if the file exists
			if(file_exists(WEBAPP_ROOT . "/models/" . strtolower($name) . ".php")) {
				require_once(WEBAPP_ROOT . "/models/" . strtolower($name) . ".php");
			} else {
				throw new Exception("Model not found: $name");
			}
		}
		
		$mdl = new $modelClass();
		$mdl->__init($name);
		return $mdl;

	} 
	
	/**
	 * See if we have a model by the given name. If it exists, return it, if not, return null
	 */
	public static function getModelIfExists($name) {
		try {
			return Model::getModel($name);
		} catch(Exception $e) {
			return null;
		}
	}
	
	/*====================================*
	 * END STATIC STUFF
	 *====================================*/
	 
	/**
	 * Association arrays. These should be overriden by model implementations
	 */
	var $hasMany = array();
	var $hasOne = array();
	
	/**
	 * Validators for the model. By default, there are no validators and this
	 * should be overriden by the model implementations 
	 */
	var $validators = array();

	/**
	 * Store the names of the members in this model for access after model
	 * has been inited. This contains only the fields loaded
	 */
	private $fields = array();
	
	/**
	 * The original values that were loaded from the database. This is used to compare what needs to be updated, and what not
	 * on save()
	 */
	private $originalValues = array();
	
	/**
	 * The name of our model
	 */
	private $modelName = null;
	
	/**
	 * Errors that may have been triggered when validating this model
	 */
	public $errors = null;
	
	/**
	 * Model constructor
	 */
	private function __init($modelName) {
		
		$cacheTable = (property_exists('AppConfiguration', 'TABLECACHE') ? AppConfiguration::$TABLECACHE : true);
		
		$this->modelName = $modelName;
 		$structure = "ModelCache_" . strtolower($modelName);
 		if(!class_exists($structure)) {
	 		if(file_exists(WEBAPP_ROOT . "/tmp/modelcache/" . md5($modelName) . ".php") &&
			 		$cacheTable) {
		 		require_once(WEBAPP_ROOT . "/tmp/modelcache/" . md5($modelName) . ".php");
	 		} else {
		 		$this->__cacheTable($modelName);	
		 		require_once(WEBAPP_ROOT . "/tmp/modelcache/" . md5($modelName) . ".php");
	 		}
 		} 		
		$this->modelCache = new $structure();
		$this->modelStructure = $this->modelCache->getModelStructure();
	}
	
	public function save($data = array()) {
		
		/**
		 * If we have original values, means that this data was loaded from the database
		 * so we call update
		 */
		if(!empty($this->originalValues)) {
			return $this->__update();
		} else {
			return $this->__insert($data);
		}
		
	}
	
	/**
	 * Deletes this model from the database
	 * 
	 * If the parameter is true, then any "subrows" (meaning matching rows in a hasMany -table)
	 * will be deleted if delete = true in the hasMany -defition. This is default behaviour, 
	 * call with "false" to not delete subrows.
	 * 
	 * Subrows will be deleted only on single delete. Using conditions will automatically omit deleting
	 * subrows
	 */
	public function delete($conditions = array(), $deleteSubrows = true) {
		
		if(!empty($conditions)) {
			
			$query = "delete from `%s` where %s";

			$cond = array();			
			foreach($conditions as $field => $value) {
				$cond[] = "`$field`=" . $this->_prepareField($value);
			}
			
			/**
			 * See if we have any model listeners that listen to the DELETE event of this model
			 */
			$model_listeners = array();
			$model_items = null;
			if(!empty(AppConfiguration::$MODEL_LISTENERS[$this->modelName])) {
				foreach(AppConfiguration::$MODEL_LISTENERS[$this->modelName] as $listener) {
					if(in_array("DELETE", $listener["events"])) {
						$model_listeners[] = $listener;
					}
				}
				if(!empty($model_listeners)) {
					// We need to load the data to be able to hand it to the listeners
					$model_items = Datasource::query(sprintf("select * from `%s` where %s", $this->modelName, join($cond, " AND ")));
				}
			}
			
			Datasource::query(
				sprintf($query,
						$this->modelName,
						join($cond, " AND "))
			);
			
			if(!empty($model_listeners)) {
				// Fire off events to the listeners
				foreach($model_listeners as $listener) {
					$listener_class = $listener["listener"];
					$listener_file = WEBAPP_DIR . "/models/listeners/" . Inflector::decamelize($listener["listener"]) . ".php";
					if(!class_exists($listener_class)) {
						require_once($listener_file);
					}
					$listener_impl = new $listener_class();
					foreach($model_items as $item) {
						// Invoke the listener
						$listener_impl->delete($item);
					}
				}
			}
			
			
		} else {
		
			$query = "delete from `%s` where `%s`=%s";
			
			Datasource::query(
				sprintf($query, $this->modelName, 
					$this->modelCache->getPrimaryKey(),
					$this->originalValues[$this->modelCache->getPrimaryKey()])
			);
			
			if($deleteSubrows) {
				
				// Go through associated models and delete rows, if necessary
				$associatedModels = array_merge($this->hasMany, $this->hasOne);
				foreach($associatedModels as $key => $value) {
					$sbModelName = (is_array($value) ? $key : $value);
					
					if(is_array($value)) {
						// Subrows with no details will be deleted
						$fieldname = $this->modelName . "_id";
					} else {
						// If there's more details, delete only if delete => true
						if($value["delete"]) {
							$fieldname = $value["field"];
						} else {
							// If not, continue to next associated model
							continue;
						}
					}
					Datasource::query(
						sprintf($query, $sbModelName, 
							$fieldname, 
							$this->originalValues[$this->modelCache->getPrimaryKey()])
					);	
					
				}
				
			}
		}
		
	}
	
	/**
	 * Loads a single row from the datasource.
	 * 
	 * To define parameters, provide an array containing the conditions. To do simple
	 * conditions just provide an associative array
	 * with key-value -pairs that match to the conditions. E.g.:
	 * 
	 * $data = $this->load(array("id" => 1234));
	 * 
	 * Loads the row with ID = 1234.
	 */
	public function load($conditions = array(), $deep = false) {
		
		// Limit query to one row
		if(!isset($conditions["limit"])) {
			$conditions["limit"] = "1";
		}
		
		$results = $this->__loadData($conditions, $deep);
		
		// __loadData returns an array, and we want to return an object from this method
		// so return only [0]
		return $results[0];
		
	}
	
	/**
	 * Loads all matching rows from the database
	 * 
	 * To define parameters, provide an array containing the conditions. To do simple
	 * conditions, with default sorting and returning all rows, just provide an associative array
	 * with key-value -pairs that match to the conditions. E.g.:
	 * 
	 * $data = $this->load(array("id" => 1234));
	 * 
	 * Loads the row with ID = 1234.
	 * 
	 * To sort or to limit the query, the conditions need multiple parameters. Provide an array that
	 * can contain the following things:
	 * 
	 * - conditions -> The query conditions (like above)
	 * - order -> Sorting
	 * - limit -> Limit the query to a number of rows
	 * - offset -> Return rows starting from offset
	 * 
	 * Example:
	 * 
	 * $data = $this->loadAll(array(
	 * 				"conditions" => array("color" => "green"), 
	 * 				"order" => array("sort" => "desc", "field" => "date"), 
	 * 				"limit" => 10, 
	 * 				"offset" => 10
	 * 				));
	 */
	public function loadAll($conditions = array(), $deep = false) {
		
		return $this->__loadData($conditions, $deep);
		
	}
	
	private function __loadData($conditions, $deep) {
		
		$sql = $this->__createSQL($conditions, $deep);
		
		/* Execute the query to the datasource */
		$results = Datasource::query($sql);

		/* Create models from the query results */
		$dataPopulatedModels = array();
		/* 
		 * Yes, there is overheader here, as the model -object used for querying 
		 * is actually not the one then being returned by the load() -call
		 */
		foreach($results as $row) {
			$model = Model::getModel($this->modelCache->getModelSource());
			foreach($this->modelCache->getFieldsForQuery() as $field) {
				$model->$field = $row[$field];
				$model->fields[] = $field;
				$model->originalValues[$field] = $row[$field];
				
				
			}
			
			if($deep) {
				
				/*
				 * 1...N relationships
				 */
				if(is_array($this->hasMany)) {
					foreach($this->hasMany as $key => $value) {
						$name = is_array($value) ? $key : $value;
						if(is_array($value)) {
							$model->$name = Model::getModel($name)->loadAll(array($value["field"] => 
								$row[$this->modelCache->getPrimaryKey()]));
						} else {
							$model->$name = Model::getModel($name)->loadAll(array($this->modelName . "_id" => 
								$row[$this->modelCache->getPrimaryKey()]));
						}
					}
				}
				
				/*
				 * 1...1 relationships
				 */
				if(is_array($this->hasOne)) {
					foreach($this->hasOne as $key => $value) {
						$name = is_array($value) ? $key : $value;
						if(is_array($value)) {
							$model->$name = Model::getModel($name)->load(array($value["field"] => 
								$row[$this->modelCache->getPrimaryKey()]));
						} else {
							$model->$name = Model::getModel($name)->load(array($this->modelName . "_id" => 
								$row[$this->modelCache->getPrimaryKey()]));
						}		
					}			
				}
				
			}
			
			
			$dataPopulatedModels[] = $model;
		}
		return $dataPopulatedModels;
	}
	
	private function __insert($data) {
		
		if(!$this->__validate($data)) {
			return false;
		}

		$insertBaseQuery = "insert into `%s` (%s) values (%s)";
		
		$fieldNames = array();
		$dataFields = array();
		
		foreach($this->modelCache->getFieldsForQuery() as $field) {
			if(isset($data[$field])) {
				$fieldNames[] = "`$field`";
				$dataFields[] = $this->__prepareField($field, $data[$field]);
			}
		}
		
		$id = Datasource::query(sprintf(
								 $insertBaseQuery,
								 $this->modelName,
								 join($fieldNames, ","),
								 join($dataFields, ",")
								));
								
		return $id;
		
	}
	
	private function __update() {
		
		if(!$this->__validate()) {
			return false;
		}
		
		$updateQueryBase = "update `%s` set %s where `%s`=%i";
		
		/*
		 * Go through all the fields to see what's changed, we only want to update
		 * those to save processing from the DB engine
		 */
		$updatedFields = array();
		foreach($this->fields as $field) {
			if($this->$field != $this->originalValues[$field]) {
				$updatedFields[] = "`$field`=" . $this->__prepareField($field, $this->$field);
			}
		}
		
		/*
		 * Create the actual query
		 */
		$query = sprintf(
					$updateQueryBase, 
					$this->modelName, // Target 
					join($updatedFields, ","), // Fields to update 
					$this->modelCache->getPrimaryKey(),
					$this->originalValues[$this->modelCache->getPrimaryKey()] // Primary key
					);
					
		Datasource::query($query);
		
		return true;
		
	}
	
	/**
	 * Validate the data before storing it
	 */
	private function __validate($data = array()) {
		
		/* Empty any existing errors */
		$this->errors = array();
		
		if(empty($data)) {
			foreach($this->fields as $field) {
				$data[$field] = $this->$field;
			}
		}
		
		if(is_array($this->validators)) {
			foreach(array_keys($this->validators) as $field) {
				foreach($this->validators[$field] as $validator) {
				
					$method = $validator["validator"];
					$params = (empty($validator["params"]) ? array() : $validator["params"]);
					
					// Can't use checkUnique with update, as it will always fail
					if(!empty($this->originalValues) && $method == 'checkUnique')
						continue;
					
					if(!Validator::$method($data[$field], $params)) {
						$this->errors[$field] = $validator["error"];
					}
				}
				
			}
		}
		
		return empty($this->errors);
	}
	
	 
 	private function __createSQL($query) {
 		
 		// Build the query
 		$sqlQuery = "select ";

		if(!empty($query["conditions"])) {
	 		if(isset($query["fields"])) {
 				$sqlQuery .= "`" . $this->modelName . "`.`" . join($query["fields"], "`,`" . $this->modelName . "`.`") . "` from `" .
	 				$this->modelCache->getModelSource() . "`";
	 		} else {
		 		$sqlQuery .= "* from `" .
			 		$this->modelCache->getModelSource() . "`";	 	
	 		}
		} else {
			$sqlQuery .= "* from `" . $this->modelCache->getModelSource() . "`";	 				
			$qTemp = $query;
			$single = ($qTemp["limit"] == 1);
			unset($qTemp["limit"]);			
			$query = array("conditions" => $qTemp);
			if($single) {
				$query["limit"] = 1;
			}
		}
 
  		$whereConditions = array();
 		if(!empty($query["conditions"])) {
 			foreach($query["conditions"] as $field => $value) {
 				// First sanitize the value, if necessary
 				$negation = false;
 				$operator = "=";
 				if(preg_match("/^!/", $field)) {
 					$field = substr($field, 1);
 					$negation = true;
 					$operator = "=";
 				} else if(preg_match("/^</", $field)) {
 					$operator = substr($field, 0, 1);
 					$field = substr($field, 1);
 				} else if(preg_match("/^>/", $field)) {
 					$operator = substr($field, 0, 1);
 					$field = substr($field, 1); 					
 				}
 				if(is_array($value)) {
 					// First see if this is a subquery
 					if(!empty($value["subquery"])) {
 						$subModel = new Model($value["target"]);
 						$whereConditions[] = "$field " . ($negation ? "not" : "")  . " in (" . $subModel->createSQL($value["subquery"]) . ")";
 					} else {
 						$valueArray = array();
 						foreach($value as $val) {
 							$valueArray[] = $this->modelCache->sanitize($field, $val);
 						}
 						$whereConditions[] = "`" . $this->modelName . "`.`$field` " . ($negation ? "not" : "")  . " in (" . join($valueArray,",") . ")";
 					}
 				} else {
					$value = $this->modelCache->sanitize($field, $value);
					$whereConditions[] = "`" . $this->modelName . "`.`$field` " . ($negation ? "!" : "")  . "$operator $value";
 				}
 			}
 			$sqlQuery .= " where " . join($whereConditions, " AND ");
 			
 		}
 		if(!empty($query["order"])) {
 			$sqlQuery .= " order by " . $query["order"]["field"] . " " . $query["order"]["sort"];
 		}
 		if(!empty($query["limit"])) {
 			$sqlQuery .= " limit " . $query["limit"] . (empty($query["offset"]) ? "" : ", " . $query["offset"]);
 		}
 		
 		return $sqlQuery;
 		
 	}
 	
 	/*
 	 * Datasource helper / preparation functions
 	 */
 	private function __prepareField($fieldName, $data) {
 		if($this->modelCache->isNumeric($fieldName)) {
 			if(!is_numeric($data)) {
 				return "NULL";
 			}
 			return $data;
 		}
 		return "\"" . Datasource::escape($data) . "\"";
 	}
 	
 	/*
 	 * Model caching functions
 	 */
 	
 	private function __cacheTable($tbName) {
		$rs = Datasource::query("describe `" . Datasource::escape($tbName) . "`");
		ob_start();
		echo "<?class ModelCache_" . $tbName . " extends ModelCache {\n";
		echo "public function ModelCache_$tbName() {\n";
		echo "\t\$this->modelSource = '$tbName';\n";
		foreach($rs as $row) {
			echo "\$this->modelStructure['" . $row["Field"] . "'] = array();\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['is_id'] = " . ($row["Key"] == 'PRI' ? "true" : "false") . ";\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['type'] = '" . $this->_parseType($row["Type"]) . "';\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['null'] = " . ($row["Null"] == 'NO' ? "false" : "true") . ";\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['maxlength'] = " . $this->_parseLength($row["Type"]). ";\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['default'] = " . $this->_getDefault($row) . ";\n";
		}
		echo "}\n";
		echo "\n}?>\n";
		$cacheContents = ob_get_contents();
		ob_end_clean();
		file_put_contents(WEBAPP_ROOT . "/tmp/modelcache/" . md5($tbName) . ".php", $cacheContents);
 	}
 	
	private function _getDefault($row) {
		if($row["Default"] == "") {
			return "null";
		}
		if($this->parseType($row["Type"]) == 'int' || 
			$this->parseType($row["Type"]) == 'double' ||
			$this->parseType($row["Type"]) == 'float') {
			return $row["Default"];
		}
		return "'" . addcslashes($row["Default"], "'") . "'";
	}
	
	private function _parseLength($t) {
		if($this->_parseType($t) == 'enum') {
			return -1;
		}
		if(strpos($t, "(") !== false) {
			$type = substr($t, strpos($t, "(") + 1, strpos($t, ")") - strpos($t, "(") - 1);
			return $type;
		}
		return "-1";
	}
	
	private function _parseType($t) {
		$type = $t;
		if(strpos($t, "(") !== false) {
			$type = substr($t, 0, strpos($t, "("));
		}
		return $type;
	} 	
	
	
}

?>
