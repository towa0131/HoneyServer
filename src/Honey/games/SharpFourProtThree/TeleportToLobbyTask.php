<?php

namespace Honey\games\SharpFourProtThree;

use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\PluginTask;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Position;

use Honey\Main;
use Honey\PlayerModule;

class TeleportToLobbyTask extends PluginTask{

	protected $owner;
	/** @var Player $player */
	protected $player;
	/** @var Position $position */
	protected $position;

	public function __construct(PluginBase $owner, Player $player, Position $position){
		parent::__construct($owner);
		$this->player = $player;
		$this->position = $position;
	}

	public function onRun(int $currentTick){
		if(Server::getInstance()->getPlayer($this->player->getName()) !== null){
			$this->player->teleport($this->position);
			$this->player->setHealth(20);
			$this->player->getInventory()->clearAll();
			$this->player->removeAllEffects();
			PlayerModule::getInstance()->sendLobbyItem($this->player);
		}
	}
}