<?php
 
namespace Honey\customUI\windows;

class CustomForm implements \JsonSerializable{
	
	protected $title = "";
	protected $elements = [];
	protected $iconURL = "";

	public function __construct($title){
		$this->title = $title;
	}
	
	public function addElement($element){
		$this->elements[] = $element;
	}
	
	public function addIconUrl($url){
		$this->iconURL = $url;
	}
	
	final public function jsonSerialize(){
		$data = [
			"type" => "custom_form",
			"title" => $this->title,
			"content" => []
		];
		if ($this->iconURL != ""){
			$data["icon"] = [
				"type" => "url",
				"data" => $this->iconURL
			];
		}
		foreach ($this->elements as $element){
			$data["content"][] = $element;
		}
		return $data;
	}
}