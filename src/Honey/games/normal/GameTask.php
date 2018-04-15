<?php

namespace Honey\games\normal;

use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\PluginTask;

use Honey\games\utils\TimeManager;

class GameTask extends PluginTask{

	protected $owner;

	protected $timeManager;

	public function __construct(PluginBase $owner, $playerA, $playerB){
		$this->owner = $owner;
		$this->playerA = $playerA;
		$this->playerB = $playerB;
		$this->timeManager = new TimeManager();
	}

	public function onRun(int $tick){
		$playerAName = $this->playerA->getName();
		$playerBName = $this->playerB->getName();
		$this->timeManager->plusTime();

		if($this->timeManager->getTime() >= 600){
			$this->owner->endGame($this->playerA, $this->playerB, true);
		}

		$minute = floor($this->timeManager->getTime() / 60) % 60;
		$second = $this->timeManager->getTime() % 60;

		$time = $second >= 10 ? $minute . ":" . $second : $minute . ":0" . $second;

		$message = "          §a----- " . $this->owner->getName() . " -----" .
				PHP_EOL .
				"          §bTarget §f: " . $playerBName . 
				PHP_EOL .
				"          §bTargetHP §f: " . round((float)$this->playerB->getHealth(), 1) .
				PHP_EOL .
				"          §bTime §f: " . $time .
				PHP_EOL .
				"          §a-----------------------";
		$this->playerA->sendPopup($message);
		$message = "          §a----- " . $this->owner->getName() . " -----" .
				PHP_EOL .
				"          §bTarget §f: " . $playerAName . 
				PHP_EOL .
				"           §bTargetHP §f: " . round((float)$this->playerA->getHealth(), 1) .
				PHP_EOL .
				"          §bTime §f: " . $time .
				PHP_EOL .
				"          §a-----------------------";
		$this->playerB->sendPopup($message);
	}
}