<?php

namespace Honey\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Utils;

use pocketmine\Player;
use pocketmine\Server;

class ReconnectCommand extends PluginCommand{

	/**
	   * @param PluginBase
	   */
	public function __construct(PluginBase $owner){
		parent::__construct("reconnect", $owner);
		$this->setDescription("サーバーへ再接続します。");
		$this->setUsage("/reconnect");
		$this->setPermission("admin.command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){
			$ip = Utils::getURL("http://ifconfig.me/ip");
			if($ip !== false and trim($ip) != ""){
				$sender->transfer(trim($ip), 19132);
				return true;
			}
		}
		return false;
	}
}