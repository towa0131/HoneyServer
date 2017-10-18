<?php

namespace  Honey\customUI\elements;

class Toggle extends Elements{
	
	protected $defaultValue = false;
	
	public function __construct($text, bool $value = false){
		$this->text = $text;
		$this->defaultValue = $value;
	}
	
	public function setDefaultValue(bool $value){
		$this->defaultValue = $value;
	}
	
	public function jsonSerialize(){
		return [
			"type" => "toggle",
			"text" => $this->text,
			"default" => $this->defaultValue
		];
	}
}