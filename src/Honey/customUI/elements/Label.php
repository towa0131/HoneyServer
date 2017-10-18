<?php
 
namespace  Honey\customUI\elements;

class Label extends Elements{
	
	public function __construct($text){
		$this->text = $text;
	}
	
	final public function jsonSerialize(){
		return [
			"type" => "label",
			"text" => $this->text
		];
	}
}