<?php

class UserModelListener extends ModelListener {
	
	public function insert($data) {
		
		$m = new MailUtil("test");
		$m->set("username", $data["username"]);
		$m->send(array($data["email"]), "Thanks for singing up", "no-reply@my-cool-service.com");
	
	}
	
}

?>
