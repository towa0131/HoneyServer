<?php
 
namespace Honey\customUI\windows;

class ModalWindow implements \JsonSerializable{
	
	protected $title = "";
	protected $content = "";
	protected $trueButtonText = "";
	protected $falseButtonText = "";

	public function __construct($title, $content, $trueButtonText, $falseButtonText){
		$this->title = $title;
		$this->content = $content;
		$this->trueButtonText = $trueButtonText;
		$this->falseButtonText = $falseButtonText;
	}
	
	final public function jsonSerialize(){
		return [
			"type" => "modal",
			"title" => $this->title,
			"content" => $this->content,
			"button1" => $this->trueButtonText,
			"button2" => $this->falseButtonText,
		];
	}
}