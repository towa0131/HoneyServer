<?php

namespace  Honey\customUI\elements;

class StepSlider extends Elements{
	
	protected $steps = [];
	protected $defaultStepIndex = 0;
	
	public function __construct($text, $steps = []){
		$this->text = $text;
		$this->steps = $steps;
	}
	
	public function addStep($stepText, $isDefault = false){
		if ($isDefault){
			$this->defaultStepIndex = count($this->steps);
		}
		$this->steps[] = $stepText;
	}
	
	public function setStepAsDefault($stepText){
		$index = array_search($stepText, $this->steps);
		if ($index === false){
			return false;
		}
		$this->defaultStepIndex = $index;
		return true;
	}
	
	public function setSteps($steps){
		$this->steps = $steps;
	}
	
	final public function jsonSerialize(){
		return [
			"type" => "step_slider",
			"text" => $this->text,
			"steps" => array_map("strval", $this->steps),
			"default" => $this->defaultStepIndex
		];
	}
}