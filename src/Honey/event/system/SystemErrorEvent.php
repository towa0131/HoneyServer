<?php

namespace Honey\event\system;

use pocketmine\event\server\ServerEvent;

class SystemErrorEvent extends ServerEvent{

	public static $handlerList = null;

	/** @var string */
	private $errno;
	/** @var string */
	private $errmsg;

	public function __construct($errno, $errmsg){
		$this->errno = $errno;
		$this->errmsg = $errmsg;
	}

	/**
	   * @return string
	   */
	public function getErrNo(){
		return $this->errno;
	}

	/**
	   * @return string
	   */
	public function getErrMsg(){
		return $this->errmsg;
	}
}