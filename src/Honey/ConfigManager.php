<?php

namespace Honey;

use pocketmine\utils\Config;

class ConfigManager{

	private static $config = [];

	public static function register(string $name, Config $config){
		if(!isset(self::$config[$name])){
			self::$config[$name] = $config;
			return true;
		}
		return false;
	}

	public static function unregister(string $name, bool $save = true){
		if(isset(self::$config[$name])){
			if($save){
				self::$config[$name]->save();
			}
			unset(self::$config[$name]);
			return true;
		}
		return false;
	}

	public static function get(string $name){
		return self::$config[$name] ?? null;
	}
}