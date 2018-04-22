<?php

namespace Honey\games;

class DuelManager{

	private static $duelPlayers = [];

	public static function addDuelRequest(string $sender, string $target, string $gameName){
		self::$duelPlayers[$sender] = [$target, $gameName];
	}

	public static function removeDuelRequest(string $name){
		foreach(self::$duelPlayers as $sender => $data){
			if($sender === $name || $data[0] === $name){
				unset(self::$duelPlayers[$sender]);
				return true;
			}
		}
		return false;
	}

	public static function hasDuelRequest(string $name){
		foreach(self::$duelPlayers as $data){
			if($data[0] === $name){
				return true;
			}
		}
		return false;
	}

	public static function getSenderByTarget(string $name){
		foreach(self::$duelPlayers as $sender => $data){
			if($data[0] === $name){
				return $sender;
			}
		}
		return null;
	}

	public static function getTargetBySender(string $name){
		foreach(self::$duelPlayers as $sender => $data){
			if($sender === $name){
				return $data[0];
			}
		}
		return null;
	}

	public static function getGameNameByTarget(string $name){
		foreach(self::$duelPlayers as $data){
			if($data[0] === $name){
				return $data[1];
			}
		}
		return null;
	}

	public static function getGameNameBySender(string $name){
		return self::$duelPlayers[$name][1] ?? null;
	}

	public static function hasDuelRequestSent(string $name){
		return isset(self::$duelPlayers[$name]);
	}
}