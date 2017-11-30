<?php

namespace Honey\utils;

use pocketmine\Server;

use Honey\Main;

use Honey\event\system\SystemErrorEvent;

class Utils{

	public static function callError($errno){
		$file = __DIR__ . "/../documents/ErrNo.txt";
		$txt = file_get_contents($file);
		$errors = explode("\n", $txt);
		foreach($errors as $e){
			$data = explode(":", $e);
			if($data[0] === $errno){
				Server::getInstance()->getPluginManager()->callEvent(new SystemErrorEvent($data[0], $data[1]));
				return true;
			}
		}
		Server::getInstance()->getPluginManager()->callEvent(new SystemErrorEvent("#000", "Unknown Error"));
		return false;
	}
}