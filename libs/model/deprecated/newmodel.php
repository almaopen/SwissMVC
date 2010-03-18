<?php
/*
 * Created on 20.11.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class Model {
 	
 	public $data = array();
 	
 	var $modelStructure = null;
 	var $modelCache = null;
 	
 	var $modelName = null;
 	
 	private $_isLoadedData = false;
 	
 	var $validators = array(
 	/*
 		"name" => array(
 					array("validator" => "mandatory", "error" => "Kentt� on pakollinen", "params" => array("min" => 3)
 				  )
 			)
 			*/
 	);
 	
 	public function Model($modelName) {
 		$this->modelName = $modelName;
 		$structure = "ModelCache_" . strtolower($modelName);
		if(file_exists(WEBAPP_ROOT . "/tmp/modelcache/" . md5($modelName) . ".php") &&
			AppConfiguration::$TABLECACHE) {
			require_once(WEBAPP_ROOT . "/tmp/modelcache/" . md5($modelName) . ".php");
		} else {
			$this->cacheTable($modelName);	
 			require_once(WEBAPP_ROOT . "/tmp/modelcache/" . md5($modelName) . ".php");
		} 		
		$this->modelCache = new $structure();
		$this->modelStructure = $this->modelCache->getModelStructure();
		$this->initModel();
 	}
 	
 	public function initModel() {}
 	
	public static function get($modelName) {
		return new Model($modelName);
	}
 	
 	public function delete($data) {
 		
		if(empty($data[$this->modelCache->getPrimaryKey()])) {
			SimpleMVCErrors::generalError("Cannot delete object type of " . $this->modelName . " as data has no primary key set");
		}
		
		Datasource::query("delete from " . $this->modelName . " where " . $this->modelCache->getPrimaryKey() . 
							"=" . $this->modelCache->sanitize($this->modelCache->getPrimaryKey(), $data[$this->modelCache->getPrimaryKey()]));
	 		
 	}
 	
 	public function saveWithData($data) {
 		$this->data = $data;
 		$this->_isLoadedData = false;
 		$this->save();
 	}
 	
 	public function save() {
 		// See if this is old data
 		if($this->_isLoadedData) {
	 		if($this->modelCache->getPrimaryKey() == null) {
		 		SimpleMVCErrors::generalError("Cannot save " . $this->modelName . " as table has no primary key defined!");
	 		} 		
			$sql = "update " . $this->modelName . " set ";
			$updateColumns = array();
			foreach($this->data as $key => $value) {
				if($this->modelCache->hasField($key)) {
					$updateColumns[] = "$key=" . $this->modelCache->sanitize($key, $value);
				}
			}
			$sql .= join($updateColumns, ",");
			$sql .= " where " . $this->modelCache->getPrimaryKey() . "=" . 
					$this->modelCache->sanitize($this->modelCache->getPrimaryKey(), 
							$this->data[$this->modelCache->getPrimaryKey()]);
			Datasource::query($sql);
			return true;
 		} else {
 			$sql = "insert into " . $this->modelName . "(" .
				join(array_keys($this->data), ",") . ") VALUES (";
			$values = array();
			foreach(array_keys($this->data) as $field) {
				if($this->modelCache->hasField($field)) {
					$value = $this->modelCache->sanitize($field, $this->data[$field]);
					$values[] = $value;
				}
			}
			$sql .= join($values, ",") . ")";
			$id = Datasource::query($sql);
			$this->data[$this->modelCache->getPrimaryKey()] = $id;
			return $id;
 		}
 	}
 	
 	public function construct($input, $action) {
 		$objectData = array();
 		$errors = 0;
 		if($this->modelCache->getPrimaryKey() != null &&
 			intval($input[$this->modelCache->getPrimaryKey()]) != 0) {
 			// Start by loading the old data
 			$this->_isLoadedData = true;
 			$objectData = $this->load(array("conditions" => 
 					array($this->modelCache->getPrimaryKey() => 
	 					$input[$this->modelCache->getPrimaryKey()])));
	 		if(empty($objectData)) {
	 			$this->_isLoadedData = false;
	 		}		
 		}
 		foreach($this->modelCache->getFields() as $fieldName) {
 			if(!empty($input[$fieldName])) {
 				// echo "Setting $fieldName";
 				$objectData[$fieldName] = $input[$fieldName];
 			} else {
 				// Set to empty if there was already data and this was empty
 				if(!$this->modelCache->isNumeric($fieldName)) {
 					if(!empty($objectData[$fieldName]) && isset($input[$fieldName])) 
	 					$objectData[$fieldName] = "";
 				} else {
 					if($input[$fieldName] != '') {
 						$objectData[$fieldName] = $input[$fieldName];
 					}
 				}
 			}
 			// Validate
 			
			if(!empty($this->validators[$fieldName])) {
				$validator = $this->validators[$fieldName];
				foreach($validator as $validatorFunctions) {
					$method = $validatorFunctions["validator"];
					$params = (empty($validatorFunctions["params"]) ? array() : $validatorFunctions["params"]);
					
					// Can't use checkUnique with update, as it will always fail
					if($this->_isLoadedData && $method == 'checkUnique')
						continue;
						
					if(!Validator::$method($objectData[$fieldName], $params)) {
						// echo "Validator $method failed";
						$action->set("error_$fieldName", $validatorFunctions["error"]);
						$action->set("hasSaveErrors", true);
					} else {
						// echo "Validator $method ok";
					}
				}
			}
 		}
 		$this->data = $objectData;
 		// print_r($this->data);
 		
		return !$action->_contextVariables["hasSaveErrors"];
 	}
 	
	public function loadMultiTable($query, $groupBy, $extraTable, $extraColumns) {
		$rows = Datasource::query($query);
		$result = array();
		foreach($rows as $row) {
			if($result[$row[$groupBy]] != null) {
				$subRow = array();
				foreach($extraColumns as $col) {
					$subRow[$col] = stripslashes($row[$col]);
				}
				if(!is_array($result[$row[$groupBy]][$extraTable])) {
					$result[$row[$groupBy]][$extraTable] = array();
				}
				$result[$row[$groupBy]][$extraTable][] = $subRow;
			} else {
				$result[$row[$groupBy]] = array();
				foreach(array_keys($row) as $colHeader) {
					if(!in_array($colHeader, $extraColumns)) {
						$result[$row[$groupBy]][$colHeader] = stripslashes($row[$colHeader]);
					}
				}
				$subRow = array();
				foreach($extraColumns as $col) {
					$subRow[$col] = $row[$col];
				}
				$result[$row[$groupBy]][$extraTable] = array( $subRow );
			}
		}
		return $result;
	} 	
 	
 	public function createSQL($query) {
 		
 		// Build the query
 		$sqlQuery = "select ";

		if(!empty($query["conditions"])) {
	 		if(isset($query["fields"])) {
 				$sqlQuery .= join($query["fields"], ",") . " from " .
	 				$this->modelCache->getModelSource();
	 		} else {
		 		$sqlQuery .= join($this->modelCache->getFieldsForQuery(), ",") . " from " .
			 		$this->modelCache->getModelSource();	 	
	 		}
		} else {
			$sqlQuery .= join($this->modelCache->getFieldsForQuery(), ",") . " from " .
				$this->modelCache->getModelSource();	 				
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
 						$whereConditions[] = "$field " . ($negation ? "not" : "")  . " in (" . join($valueArray,",") . ")";
 					}
 				} else {
					$value = $this->modelCache->sanitize($field, $value);
					$whereConditions[] = "$field " . ($negation ? "!" : "")  . "$operator $value";
 				}
 			}
 			$sqlQuery .= " where " . join($whereConditions, " AND ");
 			// echo $sqlQuery;
 			
 		}
 		if(!empty($query["order"])) {
 			$sqlQuery .= " order by " . $query["order"]["field"] . " " . $query["order"]["sort"];
 		}
 		if(!empty($query["limit"])) {
 			$sqlQuery .= " limit " . $query["limit"] . (empty($query["offset"]) ? "" : ", " . $query["offset"]);
 		}
 		
 		return $sqlQuery;
 		
 	}
 	
 	public function loadAll($query = array()) {
 		return $this->loadWithSQL($this->createSQL($query));
 	}
 	
 	public function load($query) {
 		$query["limit"] = 1;
 		$data = $this->loadWithSQL($this->createSQL($query));
 		if(count($data) > 0) {
 			return $data[0];
 		} else {
 			return array();
 		}
 	}
 	
 	public function loadWithSQL($query) {
 		
 		$rows = Datasource::query($query);
 		$data = array();
 		
 		foreach($rows as $row) {
 			$rowContents = array();
 			foreach($row as $key => $value) {
 				$rowContents[$key] = stripslashes($value);
 			}
 			$data[] = $rowContents;
 		}
 		return $data;
 		
 	}
 	
 	/*======================================================
	 Caching functions
	 *======================================================*/
	
	private function cacheTable($tbName) {
		ob_start();
		echo "<?class ModelCache_" . $tbName . " extends ModelCache {\n";
		echo "public function ModelCache_$tbName() {\n";
		echo "\t\$this->modelSource = '$tbName';\n";
		$rs = Datasource::query("describe " . Datasource::escape($tbName));
		foreach($rs as $row) {
			echo "\$this->modelStructure['" . $row["Field"] . "'] = array();\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['is_id'] = " . ($row["Key"] == 'PRI' ? "true" : "false") . ";\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['type'] = '" . $this->parseType($row["Type"]) . "';\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['null'] = " . ($row["Null"] == 'NO' ? "false" : "true") . ";\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['maxlength'] = " . $this->parseLength($row["Type"]). ";\n";
			echo "\$this->modelStructure['" . $row["Field"] . "']['default'] = " . $this->getDefault($row) . ";\n";
		}
		echo "}\n";
		echo "\n}?>\n";
		$cacheContents = ob_get_contents();
		ob_end_clean();
		$cachePointer = fopen(WEBAPP_ROOT . "/tmp/modelcache/" . md5($tbName) . ".php", "w");
		fwrite($cachePointer, $cacheContents);
		flush($cachePointer);
		fclose($cachePointer);
	}
	
	private function getDefault($row) {
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
	
	private function parseLength($t) {
		if($this->parseType($t) == 'enum') {
			return -1;
		}
		if(strpos($t, "(") !== false) {
			$type = substr($t, strpos($t, "(") + 1, strpos($t, ")") - strpos($t, "(") - 1);
			return $type;
		}
		return "-1";
	}
	
	private function parseType($t) {
		$type = $t;
		if(strpos($t, "(") !== false) {
			$type = substr($t, 0, strpos($t, "("));
		}
		return $type;
	}
	
 	
 }
 
?>
