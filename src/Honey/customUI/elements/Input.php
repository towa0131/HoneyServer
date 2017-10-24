<?php

namespace  Honey\customUI\elements;

class Input extends Elements{
	
	protected $placeholder = "";
	protected $defaultText = "";
	
	public function __construct($text, $placeholder, $defaultText = ""){
		$this->text = $text;
		$this->placeholder = $placeholder;
		$this->defaultText = $defaultText;
	}
	
	final public function jsonSerialize(){
		return [
			"type" => "input",
			"text" => $this->text,
			"placeholder" => $this->placeholder,
			"default" => $this->defaultText
		];
	}
}