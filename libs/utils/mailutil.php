<?php

class MailUtil {
	
	private $template = null;
	
	private $variables = array();
	
	public function MailUtil($template) {
		$this->template = $template;
		if(!file_exists(WEBAPP_ROOT . "/templates/mail/html/$template.php")) {
			SimpleMVCErrors::generalError("Missing text/html mail template for mail $template (WEBAPP_ROOT/templates/mail/html/$template.php)");
		}
		if(!file_exists(WEBAPP_ROOT . "/templates/mail/txt/$template.php")) {
			SimpleMVCErrors::generalError("Missing text/plain mail template for mail $template (WEBAPP_ROOT/templates/mail/txt/$template.php)");
		}		
	}
	
	public function set($key, $val) {
		$this->variables[$key] = $val;
	}
	
	public function send($recipients, $subject, $from = null) {
		
		include('Mail.php');
		include('Mail/mime.php');
		
		if($from == null) {
			$from = "no-reply@" . $_SERVER['HTTP_HOST'];
		}
		
		$headers = array(
			"From" => $from,
			"Subject" => $subject,
			"To" => join($recipients, ", ")
		);
		
		$crlf = "\n";
		$mime = new Mail_mime();
		$mime->setTXTBody($this->requireTemplate(WEBAPP_ROOT . "/templates/mail/txt/" . $this->template . ".php"));
		$mime->setHTMLBody($this->requireTemplate(WEBAPP_ROOT . "/templates/mail/html/" . $this->template . ".php"));
		
		$body = $mime->get();
		$headers = $mime->headers($headers);
		
		$mail =& Mail::factory('smtp', array("host" => "mail.inet.fi"));
		$mail->send($recipients, $headers, $body);
		
	}
	
	private function requireTemplate($path) {
		extract($this->variables);
		ob_start();
		include($path);
		$template = ob_get_contents();
		ob_end_clean();
		return $template;
	}
	
}

?>
