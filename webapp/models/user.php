<?php

class UserModel extends Model {
	
	var $validators = array(
		"username" => 
			array(
				array("validator" => "mandatory", "error" => "You must specify an username"),
				array("validator" => "length", "error" => "The username must be at least 3 characters and maximun 10 characters",
						"params" => array("min" => 3, "max" => 10))
			),
		"email" =>
			array(
				array("validator" => "mandatory", "error" => "You must provide a valid e-mail address")
			)
							
	);
	
	var $validators = array(
	
		"username" => array(
			
		)
	
	);
	
}

?>
