<?php

namespace Honey\games\SharpTwoProtTwo;

use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\PluginTask;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Position;

use Honey\Main;

class TeleportToPvPWorldTask extends PluginTask{

	/** @var PluginBase $owner */
	protected $owner;
	/** @var Core $core */
	protected $core;
	/** @var Player $playerA */
	protected $playerA;
	/** @var Player $playerB */
	protected $playerB;
	/** @var Position $positionA */
	protected $positionA;
	/** @var Position $positionB */
	protected $positionB;

	public function __construct(PluginBase $owner, Core $core, Player $playerA, Player $playerB, Position $positionA, Position $positionB){
		$this->owner = $owner;
		$this->core = $core;
		$this->playerA = $playerA;
		$this->playerB = $playerB;
		$this->positionA = $positionA;
		$this->positionB = $positionB;
	}

	public function onRun(int $currentTick){
		$this->playerA->teleport($this->positionA);
		$this->playerB->teleport($this->positionB);
		$this->playerA->getInventory()->clearAll();
		$this->playerB->getInventory()->clearAll();
		$this->playerA->getCursorInventory()->clearAll();
		$this->playerB->getCursorInventory()->clearAll();
		$this->playerA->setHealth(20);
		$this->playerB->setHealth(20);
		$this->playerA->setFood(20);
		$this->playerB->setFood(20);
		$this->core->sendItems($this->playerA);
		$this->core->sendItems($this->playerB);
		$task = new GameTask(Main::getInstance(), $this->playerA, $this->playerB);
		Server::getInstance()->getScheduler()->scheduleRepeatingTask($task, 1*20);
		$gameId = GameIdManager::getInstance()->getGameIdByPlayer($this->playerA);
		GameIdManager::getInstance()->setTaskId($gameId, $task->getTaskId());
	}
}