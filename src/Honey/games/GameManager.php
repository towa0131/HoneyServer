<?php

namespace Honey\games;

use pocketmine\Server;

use pocketmine\plugin\PluginBase;

class GameManager{

	private static $registerGame = [];

	public static function registerGame(string $gameName, $game, PluginBase $owner, bool $registerEvent = true){
		if(!self::hasGame($gameName)){
			self::$registerGame[$gameName] = $game;
			if($registerEvent){
				Server::getInstance()->getPluginManager()->registerEvents($game, $owner);
			}
			return true;
		}
		return false;
	}

	public static function hasGame(string $gameName){
		return isset(self::$registerGame[$gameName]);
	}

	public static function getGame(string $gameName){
		return self::$registerGame[$gameName] ?? null;
	}
}