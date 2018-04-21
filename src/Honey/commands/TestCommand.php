<?php

namespace Honey\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\command\utils\InvalidCommandSyntaxException;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\entity\Skin;

use pocketmine\plugin\PluginBase;

class TestCommand extends PluginCommand{

	/**
	   * @param PluginBase
	   */
	public function __construct(PluginBase $owner){
		parent::__construct("test", $owner);
		$this->setDescription("マントを装着します。");
		$this->setUsage("/test <CapeName>");
		$this->setPermission("admin.command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$cape = @file_get_contents(__DIR__."/../entity/capes/" . $args[0] . ".txt");
		if($cape){
			$skin = $sender->getSkin();
			$sender->changeSkin(new Skin($skin->getSkinId(),
							$skin->getSkinData(),
							urldecode($cape),
							$skin->getGeometryName(),
							$skin->getGeometryData()), "", "");
			$sender->sendMessage("§a[はにい]§b" . $args[0] . "を装着しました。");
			return true;
		}
		$sender->sendMessage("§a[はにー]§4マントが存在しません。");
		return false;
	}
}