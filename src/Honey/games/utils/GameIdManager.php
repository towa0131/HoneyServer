<?php

namespace Honey\games\utils;

use pocketmine\level\Level;

class GameIdManager{

	/** @var mixed[] $gameIds */
	private static $gameIds = [];
	/** @var int $gameId */
	private static $gameId = 0;

	public static function generateGameId(int $taskId, Level $level, $instance, string $playerA, string $playerB){
		self::$gameIds[self::$gameId++] = [$taskId, $level, $instance, $playerA, $playerB];
	}

	public static function removeGameId(int $id){
		if(isset(self::$gameIds[$id])){
			unset(self::$gameIds[$id]);
			return true;
		}
		return false;
	}

	public static function hasGameId(int $id){
		return isset(self::$gameIds[$id]);
	}

	public static function hasPlayer(string $name){
		foreach(self::$gameIds as $id){
			foreach($id as $data){
				if($name === $data){
					return true;
				}
			}
		}
		return false;
	}

	public static function setTaskId(int $id, int $taskId){
		if(isset(self::$gameIds[$id])){
			self::$gameIds[$id][0] = $taskId;
			return true;
		}
		return false;
	}

	public static function getPlayersByGameId(int $id){
		return [self::$gameIds[$id][3] ?? null, self::$gameIds[$id][4] ?? null];
	}

	public static function getGameIdByName(string $name){
		foreach(self::$gameIds as $key => $id){
			if(($id[3] === $name) || ($id[4] === $name)){
				return $key;
			}
		}
		return null;
	}

	public static function getTaskIdByName(string $name){
		foreach(self::$gameIds as $id){
			foreach($id as $data){
				if($name === $data){
					return $id[0];
				}
			}
		}
		return null;
	}

	public static function getLevelByName(string $name){
		foreach(self::$gameIds as $id){
			if(($id[3] === $name) || ($id[4] === $name)){
				return $id[1];
			}
		}
		return null;
	}

	public static function isValiedGameId(int $gameId, $instance){
		if(self::$gameIds[$gameId][2] instanceof $instance){
			return true;
		}
		return false;
	}
}