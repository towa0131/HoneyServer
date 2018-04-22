<?php

namespace Honey\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\command\utils\InvalidCommandSyntaxException;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use Honey\inventory\DuelInventory;

use Honey\games\DuelManager;
use Honey\games\GameList;
use Honey\games\GameManager;

class DuelCommand extends PluginCommand{

	protected $owner;

	/**
	   * @param PluginBase
	   */
	public function __construct(PluginBase $owner){
		parent::__construct("duel", $owner);
		$this->owner = $owner;
		$this->setDescription("指定したプレイヤーにデュエルリクエストを送信します。");
		$this->setUsage("/duel <PlayerName>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		if(strtolower($args[0]) == strtolower($sender->getName())){
			$sender->sendMessage("§a[はにー]§c自分自身にデュエルリクエストを送ることはできません。");
			return false;
		}

		switch(strtolower($args[0])){
			case "accept":
				if(DuelManager::hasDuelRequest($sender->getName())){
					GameManager::getGame(DuelManager::getGameNameByTarget($sender->getName()))->entryGame($sender, [$sender, Server::getInstance()->getPlayer(DuelManager::getSenderByTarget($sender->getName()))], true);
					DuelManager::removeDuelRequest($sender->getName());
					return true;
				}
				$sender->sendMessage("§a[はにー]§cデュエルリクエストはありません。");
				return false;
				break;

			case "cancel":
				if(DuelManager::hasDuelRequestSent($sender->getName())){
					$target = Server::getInstance()->getPlayer(DuelManager::getTargetBySender($sender->getName()));
					DuelManager::removeDuelRequest($sender->getName());
					$sender->sendMessage("§a[はにー]§bデュエルリクエストをキャンセルしました。");
					$target->sendMessage("§a[はにー]§b" . $sender->getName() . "からのデュエルリクエストはキャンセルされました。");
					return true;
				}
				$sender->sendMessage("§a[はにー]§cデュエルリクエストはありません。");
				return false;
				break;
		}

		if(DuelManager::hasDuelRequestSent($sender->getName())){
			$sender->sendMessage("§a[はにー]§c既にデュエルリクエストを送信しています。");
			$sender->sendMessage("§a>> §c/duel cancel§aでキャンセルしてください。");
			return false;
		}

		if(Server::getInstance()->getPlayer($args[0]) !== null){
			$target = Server::getInstance()->getPlayer($args[0]);
			$inventory = new DuelInventory($this->owner, $sender, $target);
			$sender->addWindow($inventory);
			return true;
		}
		$sender->sendMessage("§a[はにー]§cプレイヤーが見つかりません。");
		return false;
	}
}