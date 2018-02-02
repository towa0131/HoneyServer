<?php

namespace Honey\utils;

use pocketmine\Server;

use Honey\Main;

use Honey\event\system\SystemErrorEvent;

class Utils{

	public static function callError($errno){
		$file = __DIR__ . "/../documents/ErrorList.yml";
		$yaml = file_get_contents($file);
		$errors = yaml_parse($yaml);
		foreach($errors as $key => $value){
			if($key == $errno){
				Server::getInstance()->getPluginManager()->callEvent(new SystemErrorEvent($key, $value));
				return true;
			}
		}
		Server::getInstance()->getPluginManager()->callEvent(new SystemErrorEvent("000", "Unknown Error"));
		return false;
	}
}