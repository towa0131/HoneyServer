<?php

namespace Honey\games\SharpTwoProtTwo;

use pocketmine\Player;

class GameIdManager{

	/** @var EntryManager $instance */
	private static $instance;
	/** @var mixed[] $gameIds */
	private $gameIds = [];

	public function __construct(){
		self::$instance = $this;
	}

	public function addGameId(int $id, int $taskId, Player $playerA, Player $playerB){
		$this->gameIds[$id] = [$taskId, $playerA->getName(), $playerB->getName()];
	}

	public function addNewGameId(int $taskId = 0, Player $playerA, Player $playerB){
		for($i=0;$i<10;$i++){
			if(!isset($this->gameIds[$i])){
				$this->gameIds[$i] = [$taskId, $playerA->getName(), $playerB->getName()];
				return true;
			}
		}
		return false;
	}

	public function removeGameId(int $id){
		if(isset($this->gameIds[$id])){
			unset($this->gameIds[$id]);
			return true;
		}
		return false;
	}

	public function isGameIds(int $id){
		return isset($this->gameIds[$id]);
	}

	public function hasPlayer(Player $player){
		foreach($this->gameIds as $id){
			foreach($id as $data){
				if($player->getName() === $data){
					return true;
				}
			}
		}
		return false;
	}

	public function setTaskId(int $id, int $taskId){
		if(isset($this->gameIds[$id])){
			$this->gameIds[$id][0] = $taskId;
			return true;
		}
		return false;
	}

	public function getPlayersByGameId(int $id){
		return [$this->gameIds[$id][1], $this->gameIds[$id][2]];
	}

	public function getGameIdByPlayer(Player $player){
		foreach($this->gameIds as $key => $id){
			if(($id[1] == $player->getName()) || ($id[2] == $player->getName())){
				return $key;
			}
		}
		return null;
	}

	public function getTaskIdByPlayer(Player $player){
		foreach($this->gameIds as $id){
			foreach($id as $data){
				if($player->getName() == $data){
					return $id[0];
				}
			}
		}
		return null;
	}

	public static function getInstance(){
		return self::$instance;
	}
}