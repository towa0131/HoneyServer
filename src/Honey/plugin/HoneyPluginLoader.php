<?php

namespace Honey\plugin;

use pocketmine\Server;

use Honey\Main;

class HoneyPluginLoader{

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	public function loadPlugin($file){
		if(is_dir($file) && file_exists($file . "/honey.json") && file_exists($file . "/src/")){
			$description = json_decode(file_get_contents($file . "/honey.json"), true);
			$className = $description["main"];
			require_once $file . "/src/" . $className . ".php";
			$class = str_replace("/", "\\", $className);
			if(class_exists($class, true)){
				Main::getInstance()->getLogger()->info($description["name"] . "を読み込み中...");
				$plugin = new $class();
				$plugin->onEnable();
				HoneyPluginManager::addPlugin($description["main"], $plugin);
				return true;
			}else{
				Main::getInstance()->getLogger()->error($description["name"] . "のメインクラスが見つかりませんでした。");
				return false;
			}
		}
		return false;
	}
}