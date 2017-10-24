<?php
 
namespace  Honey\customUI\elements;

class Image extends Elements{
	
	public $texture;
	public $width;
	public $height;
	
	public function __construct($texture, $width = 0, $height = 0){
		$this->texture = $texture;
		$this->width = $width;
		$this->height = $height;
	}
	
	final public function jsonSerialize(){
		return [
			"text" => "sign",
			"type" => "image",
			"texture" => $this->texture,
			"size" => [$this->width, $this->height]
		];
	}
}