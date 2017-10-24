<?php

namespace Honey\customUI\windows;

class SimpleForm implements \JsonSerializable{
	
	protected $title = "";
	protected $content = "";
	protected $buttons = [];
	
	public function __construct($title, $content = ""){
		$this->title = $title;
		$this->content = $content;
	}
	
	public function addButton($button){
		$this->buttons[] = $button;
	}
	
	final public function jsonSerialize(){
		$data = [
			"type" => "form",
			"title" => $this->title,
			"content" => $this->content,
			"buttons" => []
		];
		foreach ($this->buttons as $button){
			$data["buttons"][] = $button;
		}
		return $data;
	}
}