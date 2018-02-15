<?php

namespace Honey\games\SharpFourProtThree;

use pocketmine\Player;

class GameIdManager{

	/** @var EntryManager */
	private static $instance;
	/** @var mixed[] */
	private $gameIds = [];

	public function __construct(){
		self::$instance = $this;
	}

	public function addGameId(int $id, int $taskId, Player $playerA, Player $playerB){
		$this->gameIds[$id] = [$taskId, $playerA->getName(), $playerB->getName()];
	}

	public function addNewGameId(int $taskId, Player $playerA, Player $playerB){
		$this->gameIds[count($this->gameIds) / 2] = [$taskId, $playerA->getName(), $playerB->getName()];
	}

	public function removeGameId(int $id){
		if(isset($this->gameIds[$id])){
			$this->gameIds[$id] = [];
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

	public function getPlayersByGameId(int $id){
		return [$this->gameIds[$id][1], $this->gameIds[$id][2]];
	}

	public function getGameIdByPlayer(Player $player){
		$i = 0;
		foreach($this->gameIds as $id){
			foreach($id as $data){
				if($player->getName() === $data){
					return $i;
				}
			}
			++$i;
		}
		return null;
	}

	public function getTaskIdByPlayer(Player $player){
		$i = 0;
		foreach($this->gameIds as $id){
			foreach($id as $data){
				if($player->getName() === $data){
					return $id[0];
				}
			}
			++$i;
		}
		return null;
	}

	public static function getInstance(){
		return self::$instance;
	}
}