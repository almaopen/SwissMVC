<?php

class HtmlHelper {
	
	public static $SIMPLE_ELEMENTS = array("hr","br","img","link","meta", "input");
	
	
	private $controller = null;
	
	public function HtmlHelper($controller = null) {
		$this->controller = $controller;
	}
	
	public function formError($name) {
		if(isset($this->controller->errors[$name])) {
			return $this->element("div", 
				join($this->controller->errors[$name], "<br/>"), array("class" => "formError"));
		}
	}
	
	public function radio($name, $values, $label = null, $params = array()) {
		
		$items = $this->formError($name);
		foreach($values as $key => $value) {
			$r_params = array("type" => "radio", "name" => $name, "value" => $key);
			if($this->controller->input[$name] == $key) {
				$r_params["checked"] = "checked";
			}
			$items .= $this->element("div", $this->startElement("input", $r_params, true) . " " . $value);
		}
		
		return $this->element("div", $items, $params);
		
	}
	
	public function textarea($name, $label = null, $params = array()) {
		
		return 
			$this->element("div",
				(($label != null) ? $this->element("label", $label, array("for" => $name)) : "") .
				$this->formError($name) . 
				$this->element("div", $this->element("textarea", $this->controller->input[$name], 
								array("name" => $name, "id" => "txt_$name"))),
				$params 
			);
		
	}	
	
	public function textinput($name, $label = null, $params = array()) {
		
		return 
			$this->element("div",
				(($label != null) ? $this->element("label", $label, array("for" => $name)) : "") .
				$this->formError($name) . 
				$this->element("div", $this->startElement("input", 
					array("name" => $name, "id" => "txt_$name", "type" => "text", "value" => $this->controller->input[$name]), true)),
				$params 
			);
		
	}
	
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
			if(in_array($name, HtmlHelper::$SIMPLE_ELEMENTS)) {
				return $this->startElement($name, $params, true);
			} else {
				return $this->startElement($name, $params, false) . $this->endElement($name);
			}
		} else {
			$element = $this->startElement($name, $params);		
		}
		
		$element .= $wrap;
		$element .= $this->endElement($name);
		return $element;
		
	}
	
	/* Elements */

	public function select($name, $options, $params = array()) {
		$data .= $this->startElement("select", array_merge(array("name" => $name), $params));
		
		foreach($options as $key => $value) {
			$option_params = array($value => $key);
			if($params["selected"] == $key) {
				$option_params["selected"] = "selected";
			}
			$data .= $this->element("option", $value, $option_params);			
		}
		
		$data .= $this->endElement("select");
		
		return $data;
	}

}

?>
