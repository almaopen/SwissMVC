<?php
/*
 * Created on 7.1.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class IndexController extends Controller {
 	
 	var $bindModelName = "mvc1";
 	
 	public function _default() {
 		
 		// $this->Mvc1->save(array("name" => "Tuomas", "email" => "trinta@gmail.com", "description" => "Nerd"));
 		
 		print_r($this->loadAll());
 		
 		
 		$this->_renderView = false;
 		
 	}

 	
 }
?>
