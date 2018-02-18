<?php

namespace Honey\games\SharpFourProtThree;

use pocketmine\scheduler\PluginTask;

use Honey\games\utils\TimeManager;

class GameTask extends PluginTask{

	protected $owner;

	protected $timeManager;

	public function __construct($owner, $playerA, $playerB){
		$this->owner = $owner;
		$this->playerA = $playerA;
		$this->playerB = $playerB;
		$this->timeManager = new TimeManager();
	}

	public function onRun(int $tick){
		$playerAName = $this->playerA->getName();
		$playerBName = $this->playerB->getName();
		$this->timeManager->plusTime();
		if($this->timeManager->getTime() > 600){
			Core::getInstance()->endGame($this->playerA, $this->playerB, true);
		}
		$minute = floor($this->timeManager->getTime() / 60) % 60;
		$second = $this->timeManager->getTime() % 60;
		if($second < 10){
			$tmp = $second;
			$second2 = "0" . (string)$tmp;
		}
		if($second >= 10){
			$time = $minute . ":" . $second;
		}else{
			$time = $minute . ":" . $second2;
		}
		$message = "                         §a----- Sharp4Prot3 -----" .
				PHP_EOL .
				"                         §bTarget §f: " . $playerBName . 
				PHP_EOL .
				"                         §bTargetHP §f: " . round((float)$this->playerB->getHealth(), 1) .
				PHP_EOL .
				"                         §bTime §f: " . $time .
				PHP_EOL .
				"                         §a-----------------------";
		$this->playerA->sendPopup($message);
		$message = "                         §a----- Sharp4Prot3 -----" .
				PHP_EOL .
				"                         §bTarget §f: " . $playerAName . 
				PHP_EOL .
				"                         §bTargetHP §f: " . round((float)$this->playerA->getHealth(), 1) .
				PHP_EOL .
				"                         §bTime §f: " . $time .
				PHP_EOL .
				"                         §a-----------------------";
		$this->playerB->sendPopup($message);
	}
}