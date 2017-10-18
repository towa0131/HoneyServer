<?php

namespace  Honey\customUI\elements;

class Dropdown extends Elements{
	
	protected $options = [];
	protected $defaultOptionIndex = 0;
	
	public function __construct($text, $options = []){
		$this->text = $text;
		$this->options = $options;
	}
	
	public function addOption($optionText, $isDefault = false){
		if ($isDefault){
			$this->defaultOptionIndex = count($this->options);
		}
		$this->options[] = $optionText;
	}
	
	public function setOptionAsDefault($optionText){
		$index = array_search($optionText, $this->options);
		if ($index === false){
			return false;
		}
		$this->defaultOptionIndex = $index;
		return true;
	}
	
	public function setOptions($options){
		$this->options = $options;
	}
	
	final public function jsonSerialize(){
		return [
			"type" => "dropdown",
			"text" => $this->text,
			"options" => $this->options,
			"default" => $this->defaultOptionIndex
		];
	}
}