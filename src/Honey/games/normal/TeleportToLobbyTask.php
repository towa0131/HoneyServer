<?php

namespace Honey\games\normal;

use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\PluginTask;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Position;

use Honey\Main;
use Honey\PlayerModule;

class TeleportToLobbyTask extends PluginTask{

	protected $owner;
	/** @var Core $core */
	protected $core;
	/** @var Player $player */
	protected $player;
	/** @var Position $position */
	protected $position;

	public function __construct(PluginBase $owner, Core $core, Player $player, Position $position){
		parent::__construct($owner);
		$this->core = $core;
		$this->player = $player;
		$this->position = $position;
	}

	public function onRun(int $currentTick){
		if(Server::getInstance()->getPlayer($this->player->getName()) !== null){
			$level = $this->player->getLevel();
			if($level->getFolderName() !== Main::getInstance()->config->getNested("Level.default-world") && $level->getFolderName() !== Main::getInstance()->config->getNested("Level.wait-world")){
				Server::getInstance()->unloadLevel($level);
				$this->core->deletePvPLevel($level->getFolderName());
			}
			$this->player->teleport($this->position);
			$this->player->setHealth(20);
			$this->player->getInventory()->clearAll();
			$this->player->getCursorInventory()->clearAll();
			$this->player->removeAllEffects();
			$this->player->setSpawn(Server::getInstance()->getLevelByName(Main::getInstance()->config->getNested("Level.default-world"))->getSafeSpawn());
			PlayerModule::getInstance()->sendLobbyItem($this->player);
		}
	}
}