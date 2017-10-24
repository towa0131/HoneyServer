<?php

namespace Honey;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use Honey\commands\RegisterCommand;
use Honey\commands\TestCommand;

class CommandManager{

	/**
	   * @param PluginBase $owner
	   */
	public function __construct(PluginBase $owner){
		$this->registerCommands($owner);
	}

	/**
	   * @param PluginBase $owner
	   */
	public function registerCommands(PluginBase $owner){
		Server::getInstance()->getCommandMap()->register(RegisterCommand::class, new RegisterCommand($owner));
		Server::getInstance()->getCommandMap()->register(TestCommand::class, new TestCommand($owner));
	}
}