<?php

namespace Honey;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use Honey\commands\RegisterCommand;

class CommandManager{

	public function __construct(PluginBase $owner){
		$this->registerCommands($owner);
	}

	public function registerCommands(PluginBase $owner){
		Server::getInstance()->getCommandMap()->register(RegisterCommand::class, new RegisterCommand($owner));
	}
}