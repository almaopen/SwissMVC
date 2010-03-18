<?

class ModelCache {
	
	protected $modelStructure = null;
	protected $modelSource = null;
	
	public function initializeData(&$data) {
		$data = array();
		foreach(array_keys($this->modelStructure) as $key) {
			$data[$key] = null;
			if($this->modelStructure[$key]['default'] != null) {
				$data[$key] = $this->modelStructure[$key]['default'];
			}
		}
	}
	
	public function isNumeric($fld) {
		if($this->modelStructure[$fld]['type'] == 'int')
			return true;
		if($this->modelStructure[$fld]['type'] == 'tinyint')
			return true;
		if($this->modelStructure[$fld]['type'] == 'smallint')
			return true;
		if($this->modelStructure[$fld]['type'] == 'bit')
			return true;
		if($this->modelStructure[$fld]['type'] == 'short')
			return true;						
		return false;
	}
	
	public function getFieldsForQuery() {
		$fields = array();
		foreach(array_keys($this->modelStructure) as $fld) {
			if($this->modelStructure[$fld]["type"] == "timestamp") {
				$fields[] = "UNIX_TIMESTAMP($fld) as $fld";
			} else {
				$fields[] = $fld;
			}
		}
		return $fields;		
	}
	
	public function getFields() {
		$fields = array();
		foreach(array_keys($this->modelStructure) as $fld) {
			$fields[] = $fld;
		}
		return $fields;
	}
	
	public function hasField($fld) {
		return in_array($fld, $this->getFields());
	}
	
	public function sanitize($fld, $value) {
		if(preg_match("#text$#", $this->modelStructure[$fld]["type"]) ||
			preg_match("#char$#", $this->modelStructure[$fld]["type"]) || 
			$this->modelStructure[$fld]["type"] == "enum" || 
			$this->modelStructure[$fld]["type"] == "date" ||
			$this->modelStructure[$fld]["type"] == "timestamp") {
			$value = "'" . Datasource::escape($value) . "'";
		} else if($this->modelStructure[$fld]["type"] == "tinyint") {
			if(!(intval($value) == 0 || intval($value) == 1)) {
				SimpleMVCErrors::generalError("Given value ($value) is not a valid boolean value");
			}
		} else {
			// Assume to be numeric
			if(!is_numeric($value) && $value != 'NULL') {
				SimpleMVCErrors::generalError("$value given as numeric database input (" . $this->modelSource . ".$fld)");
			}
		}
		return $value;
	}
	
	public function getPrimaryKey() {
		foreach(array_keys($this->modelStructure) as $key) {
			if($this->modelStructure[$key]['is_id']) {
				return $key;
			}
		}
		return NULL;
	}
	
	public function getModelStructure() {
		return $this->modelStructure;
	}
	
	public function getModelSource() {
		return $this->modelSource;
	}
	
}

?>