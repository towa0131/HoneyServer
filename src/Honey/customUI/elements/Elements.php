<?php

namespace Honey\customUI\elements;

abstract class Elements implements \JsonSerializable{
	
	public function jsonSerialize(){
		return [];
	}
}