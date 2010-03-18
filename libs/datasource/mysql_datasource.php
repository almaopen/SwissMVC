<?php

/**
 * MySQL implementation of the Datasource -class
 */
class MysqlDatasource extends Datasource {
	
	private $link = null;
	
	public function _connect($configuration) {
	 	
	 	$link = mysql_connect($configuration["db.host"],
	 							$configuration["db.username"],
	 							$configuration["db.password"]);
	 	
	 	mysql_select_db($configuration["db.database"], $link);
	 	
	 	$this->link = $link;
	 	
	}
	
	public function _disconnect() {
		if($this->link != null) {
			@mysql_close($this->link);
		}
	}
	 
	 public function _escape($string) {
	 	
	 	return mysql_real_escape_string($string);
	 	
	 }
	 
	 public function _query($sql) {
	 	
	 	$result = mysql_query($sql, $this->link);
	 	if($result === false) {
	 		throw new Exception(mysql_error());
	 	}
	 
	 	if(preg_match("#^insert#i", $sql)) {
	 		$id = mysql_insert_id();
	 		return $id;
	 	}
	 	
	 	if(preg_match("#^(update|delete)#i", $sql)) {
	 		return true;
	 	}
	 	
	 	// Should be select then
	 	$rows = array();
	 	while(($row = mysql_fetch_assoc($result)) !== false) {
	 		$rows[] = $row;
	 	}
	 	
	 	return $rows;
	 	
	 }	
	
}

?>
