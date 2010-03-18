<?php
/*
 * Created on 7.1.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class Mvc1Model extends Model {

	var $hasMany = array(
							"mvc2" => array(
								"field" => "mvc1_id",
								"delete" => true // Should the "childrows" be deleted when the parent is deleted
							)
						);
	
}
 
?>
