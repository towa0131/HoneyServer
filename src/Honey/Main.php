<?php

namespace Honey;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\scheduler\CallbackTask;
use pocketmine\scheduler\PluginTask;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\item\Item;

use pocketmine\event\Listener;

use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("§a[はにー]§bプラグインを読み込んでいます...");
		if(!file_exists($this->getDataFolder())){
			$this->getLogger()->info("§a[はにー]§bコンフィグファイルを生成しています...");
			mkdir($this->getDataFolder() , 0777);
			$this->saveDefaultConfig();
		}
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
	}
}