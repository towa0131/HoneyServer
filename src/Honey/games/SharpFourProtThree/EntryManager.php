<?php

namespace Honey\games\SharpFourProtThree;

use pocketmine\Player;
use pocketmine\Server;

class EntryManager{

	/** @var EntryManager */
	private static $instance;
	/** @var Player[] */
	private static $entryPlayers = [];

	public function __construct(){
		self::$instance = $this;
	}

	public function addEntryPlayer(Player $player){
		$entryCount = count(self::$entryPlayers);
		$number = $entryCount / 2;
		$num1 = 0;
		$num2 = 0;
		$priority = false;
		$i = 0;
		foreach(self::$entryPlayers as $entryPlayer){
			if(isset($entryPlayer[0]) && !isset($entryPlayer[1])){
				self::$entryPlayers[$i][1] = $player->getName();
				$num1 = $i;
				$priority = true;
				break;
			}
			if(!isset($entryPlayer[0]) && isset($entryPlayer[1])){
				self::$entryPlayers[$i][0] = $player->getName();
				$num1 = $i;
				$priority = true;
				break;
			}
			++$i;
		}
		if(!$priority){
			while(true){
				if(!isset(self::$entryPlayers[$num1][$num2])){
					break;
				}
				$num2++;
				if($num2 > 1){
					$num2 = 0;
					$num1++;
					if($num1 > 10){
						$player->sendMessage("§a[はにー]§c満員です。");
						return;
					}
				}
			}
			self::$entryPlayers[$num1][$num2] = $player->getName();
		}
		Core::getInstance()->onEntry($player);
		if(isset(self::$entryPlayers[$num1][0]) && isset(self::$entryPlayers[$num1][1])){
			Core::getInstance()->startGame(Server::getInstance()->getPlayer(self::$entryPlayers[$num1][0]), Server::getInstance()->getPlayer(self::$entryPlayers[$num1][1]));
		}
	}

	public function removeEntryPlayer(Player $player){
		$entryCount = count(self::$entryPlayers);
		$number = $entryCount / 2;
		$num1 = 0;
		$num2 = 0;
		$completed = false;
		while(!$completed){
			if(isset(self::$entryPlayers[$num1][$num2])){
				if(self::$entryPlayers[$num1][$num2] == $player->getName()){
					self::$entryPlayers[$num1] = [];
					return true;
				}
			}
			$num2++;
			if($num2 > 1){
				$num2 = 0;
				$num1++;
			}
			if($num1 > 10){
				$completed = true;
			}
		}
		return false;
	}

	public function isEntryPlayer(Player $player){
		$entryCount = count(self::$entryPlayers);
		$number = $entryCount / 2;
		$num1 = 0;
		$num2 = 0;
		$completed = false;
		while(!$completed){
			if(isset(self::$entryPlayers[$num1][$num2])){
				if(self::$entryPlayers[$num1][$num2] == $player->getName()){
					return true;
				}
			}
			$num2++;
			if($num2 > 1){
				$num2 = 0;
				$num1++;
			}
			if($num1 > 10){
				$completed = true;
			}
		}
		return false;
	}

	public static function getInstance(){
		return self::$instance;
	}
}