<?php

namespace Honey\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

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
		$this->setPermission("admin.command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!isset($args[0])){
			$sender->sendMessage("§a[はにい]§4使用方法: /test <CapeName>");
			return false;
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
		}else{
			$sender->sendMessage("§a[はにー]§4マントが存在しません。");
			return false;
		}
		return true;
	}

}