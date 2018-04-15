<?php

namespace Honey\games\normal;

use pocketmine\Player;
use pocketmine\Server;

class EntryManager{

	/** @var Core */
	private $owner;
	/** @var Player[] */
	private $entryPlayers = [];

	public function __construct(Core $owner){
		$this->owner = $owner;
	}

	public function addEntryPlayer(Player $player){
		/* NEW ALGO */
		$new = isset($this->entryPlayers[0]) ? true : false;
		if($new){
			$num = 0;
			foreach($this->entryPlayers as &$entryPlayer){
				foreach($entryPlayer as &$ep){
					++$num;
					if(is_null($ep)){
						$ep = $player->getName();
						$this->owner->onEntry($player);
						break 2;
					}
				}
				if($num > 20){
					$player->sendMessage("§a[はにー]§c満員です。");
					return false;
				}
			}
			if(!is_null($this->entryPlayers[ceil($num / 2) - 1][$num % 2])){
				$this->owner->startGame(Server::getInstance()->getPlayer($this->entryPlayers[ceil($num / 2) - 1][0]), Server::getInstance()->getPlayer($this->entryPlayers[ceil($num / 2) - 1][1]));
			}
		}else{
			for($i=0;$i<10;$i++){
				$this->entryPlayers[] = [null, null];
			}
			$this->entryPlayers[0][0] = $player->getName();
			$this->owner->onEntry($player);
		}
		return true;
	}

	public function removeEntryPlayer(Player $player){
		/* NEW ALGO */
		foreach($this->entryPlayers as &$ep){
			foreach($ep as &$name){
				if($name === $player->getName()){
					$name = null;
					return true;
				}
			}
		}
		return false;
	}

	public function isEntryPlayer(Player $player){
		/* NEW ALGO */
		foreach($this->entryPlayers as $ep){
			foreach($ep as $name){
				if($name === $player->getName()){
					return true;
				}
			}
		}
		return false;
	}
}