<?php

namespace  Honey\customUI\elements;

class Button extends Elements{
	
	protected $imageURL = "";
	
	public function __construct($text){
		$this->text = $text;
	}
	
	public function addImage($imageURL){
		$this->imageURL = $imageURL;
	}
	
	final public function jsonSerialize(){
		$data = [
			"type" => "button",
			"text" => $this->text
		];
		if ($this->imageURL != ""){
			$data["image"] = [
				"type" => "url",
				"data" => $this->imageURL
			];
		}
		return $data;
	}
}