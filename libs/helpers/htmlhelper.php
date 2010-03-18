<?php

class HtmlHelper {
	
	public function startElement($name, $params = array(), $short = false) {
		$data = "<$name";
		foreach(array_keys($params) as $paramKey) {
			$data .= " $paramKey=\"" . $params[$paramKey] . "\"";
		}
		if($short) {
			$data .= "/";
		}
		$data .= ">";
		return $data;
	}
	
	
	public function endElement($name) {
		return "</$name>\n";
	}
	
	public function element($name, $wrap = null, $params = array()) {
		
		$element = "";
		
		if($wrap == null) {
			return $this->startElement($name, $params, true);
		} else {
			$element = $this->startElement($name, $params);		
		}
		
		$element .= $wrap;
		$element .= $this->endElement($name);
		return $element;
		
	}

}

?>
