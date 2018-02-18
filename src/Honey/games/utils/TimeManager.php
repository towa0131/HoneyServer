<?php

namespace Honey\games\utils;

class TimeManager{

	private $time;

	public function __construct(int $defaultTime = 0){
		$this->time = $defaultTime;
	}

	public function plusTime(){
		$this->time++;
	}

	public function minusTime(){
		$this->time--;
	}

	public function setTime(int $time){
		$this->time = $time;
	}

	public function getTime(){
		return $this->time;
	}

	public function resetTime(){
		$this->time = 0;
	}
}