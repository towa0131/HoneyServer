<?php

namespace Honey\games\normal;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;

use pocketmine\level\Position;

use pocketmine\item\Item;

use pocketmine\Player;
use pocketmine\Server;

use Honey\Main;

use Honey\PlayerModule;
use Honey\ItemProvider;

use Honey\account\AccountManager;

use Honey\games\GameList;

use Honey\games\utils\GameIdManager;
use Honey\games\utils\PotPvP;

abstract class Core implements PotPvP, Listener{

	/** @var PluginBase */
	private $owner;
	/** @var string */
	private $name;
	/** @var EntryManager */
	private $entryManager;

	public function __construct(PluginBase $owner){
		$this->owner = $owner;
		$this->entryManager = new EntryManager($this);
	}

	public function onEntry(Player $player){
		$player->sendMessage("§a[はにー]§b" . $this->getName() . "にエントリーしました。");
	}

	public function onDeath(PlayerDeathEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if($this->getEntryManager()->isEntryPlayer($player)){
			$gameId = GameIdManager::getGameIdByName($name);
			$players = GameIdManager::getPlayersByGameId($gameId);
			$event->setDeathMessage("");
			if($players[0] === $name){
				$this->endGame(Server::getInstance()->getPlayer($players[1]), Server::getInstance()->getPlayer($players[0]));
				return true;
			}
			if($players[1] === $name){
				$this->endGame(Server::getInstance()->getPlayer($players[0]), Server::getInstance()->getPlayer($players[1]));
				return true;
			}
		}
		return false;
	}

	public function onRespawn(PlayerRespawnEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$level = $player->getLevel();
		if(GameIdManager::hasPlayer($name)){
			if($level->getFolderName() === GameIdManager::getLevelByName($name)->getFolderName()){
				$waitLevel = Server::getInstance()->getLevelByName(Main::getInstance()->config->getNested("Level.wait-world"));
				$event->setRespawnPosition($waitLevel->getSafeSpawn());
				$player->teleport($waitLevel->getSafeSpawn());
				return true;
			}
			$event->setRespawnPosition(Server::getInstance()->getLevelByName(Main::getInstance()->config->getNested("Level.default-world"))->getSafeSpawn());
			$player->teleport(Server::getInstance()->getLevelByName(Main::getInstance()->config->getNested("Level.default-world"))->getSafeSpawn());
			return true;
		}
		return false;
	}

	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(GameIdManager::hasPlayer($name)){
			$event->setQuitMessage("");
			$gameId = GameIdManager::getGameIdByName($name);
			if(GameIdManager::isValiedGameId($gameId, $this)){
				$players = GameIdManager::getPlayersByGameId($gameId);
				if($players[0] === $player->getName()){
					$this->endGame(Server::getInstance()->getPlayer($players[1]), $player, false, true);
				}else{
					$this->endGame(Server::getInstance()->getPlayer($players[0]), $player, false, true);
				}
			}
		}
		if($this->getEntryManager()->isEntryPlayer($player)){
			$this->getEntryManager()->removeEntryPlayer($player);
		}
	}

	public function startGame(Player $playerA, Player $playerB){
		$playerA->sendMessage("§a[はにー]§bまもなく試合が始まります。");
		$playerB->sendMessage("§a[はにー]§bまもなく試合が始まります。");
		if(Server::getInstance()->loadLevel(Main::getInstance()->config->getNested("Level.wait-world")) != false){
			$level = Server::getInstance()->getLevelByName(Main::getInstance()->config->getNested("Level.wait-world"));
			$pvplevel = $this->createPvPLevel(true);
			$playerA->teleport($level->getSafeSpawn());
			$playerB->teleport($level->getSafeSpawn());
			$playerA->getInventory()->clearAll();
			$playerB->getInventory()->clearAll();
			$pvplevel = Server::getInstance()->getLevelByName($pvplevel);
			$pvplevel->setAutoSave(false);
			GameIdManager::generateGameId(0, $pvplevel, $this, $playerA->getName(), $playerB->getName());
			$gameId = GameIdManager::getGameIdByName($playerA->getName());
			$task = new TeleportToPvPWorldTask(Main::getInstance(), $this, $playerA, $playerB, new Position(289.9, 9, 249.9, $pvplevel), new Position(290.0, 9, 163.9, $pvplevel));
			Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
		}
	}

	public function endGame($winner, $loser, $timeUp = false, $quit = false){
		$taskId = GameIdManager::getTaskIdByName($winner->getName());
		Server::getInstance()->getScheduler()->cancelTask($taskId);
		$defaultLevel = Server::getInstance()->getLevelByName(Main::getInstance()->config->getNested("Level.default-world"));
		$log = "§a[はにー]§bゲームが終了しました。";
		/** EntryManager / GameIdManager */
		$this->getEntryManager()->removeEntryPlayer($winner);
		$this->getEntryManager()->removeEntryPlayer($loser);
		GameIdManager::removeGameId(GameIdManager::getGameIdByName($winner->getName()));
		$winner->sendMessage($log);
		if(!$quit){
			$loser->sendMessage($log);
		}

		if(!$timeUp){
			$log = "§c=== " . $this->getName() . " ===" .
				PHP_EOL .
				" §4Winner §f- " . $winner->getName() .
				PHP_EOL .
				PHP_EOL .
				" §1Loser §f- " . $loser->getName() .
				PHP_EOL .
				"§c==================";
			$winner->sendMessage($log);
			$honey = $this->getHoney();
			$account = AccountManager::getAccount($winner);
			if($account !== null){
				$account->addHoney($honey);
				$winner->sendMessage("§a[はにー]§e" . $honey . "はにい§a入手しました。");
			}else{
				$winner->sendMessage("§a[はにー]§cエラーが発生しました。");
			}
			$task = new TeleportToLobbyTask(Main::getInstance(), $this, $winner, $defaultLevel->getSafeSpawn());
			Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
			if(!$quit){
				$loser->sendMessage($log);
				$task = new TeleportToLobbyTask(Main::getInstance(), $this, $loser, $defaultLevel->getSafeSpawn());
				Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
			}
			return true;
		}

		$log = "§c=== " . $this->getName() . " ===" .
			PHP_EOL .
			"§a          Draw" .
			PHP_EOL .
			"§c==================";
		$winner->sendMessage($log);
		$task = new TeleportToLobbyTask(Main::getInstance(), $this, $winner, $defaultLevel->getSafeSpawn());
		Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
		if(!$quit){
			$loser->sendMessage($log);
			$task = new TeleportToLobbyTask(Main::getInstance(), $this, $loser, $defaultLevel->getSafeSpawn());
			Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
		}
		return true;
	}

	public function entryGame(Player $player){
		$player->getInventory()->clearAll();
		$item = Item::get(355, 14, 1);
		ItemProvider::getInstance()->setUndroppable($item);
		$item->setCustomName("§cエントリーをキャンセル");
		$player->getInventory()->setItem(20, $item);
		$player->getInventory()->setHotbarSlotIndex(4, 20);
		$this->getEntryManager()->addEntryPlayer($player);
	}

	public function cancelEntryGame(Player $player){
		$player->sendMessage("§a[はにー]§cエントリーをキャンセルしました。");
		$player->getInventory()->clearAll();
		PlayerModule::getInstance()->sendLobbyItem($player);
		$this->getEntryManager()->removeEntryPlayer($player);
	}

	public function isEntryGame(Player $player){
		$entry = $this->getEntryManager()->isEntryPlayer($player);
		return $entry;
	}

	public function createPvPLevel(bool $load = true){
		$name = explode(" ", microtime())[1];
		$pvplevel = Server::getInstance()->getDataPath() . "worlds/PvPMap";
		$newdir = Server::getInstance()->getDataPath() . "worlds/" . $name;
		$this->copydir($pvplevel, $newdir);
		$level = Server::getInstance()->getLevelByName($name);
		if($load){
			Server::getInstance()->loadLevel($name);
		}
		return $name;
	}

	public function deletePvPLevel(string $levelName){
		$dir = Server::getInstance()->getDataPath() . "worlds/" . $levelName;
		if(is_dir($dir) and !is_link($dir)){
			$paths = array();
			while($glob = glob($dir)){
				$paths = array_merge($glob, $paths);
				$dir .= "/*";
			}
			array_map("unlink", array_filter($paths, "is_file"));
			array_map("rmdir",  array_filter($paths, "is_dir"));
		}
	}

	public function copydir($dir, $newdir){
		if(!is_dir($newdir)){
			mkdir($newdir);
		}

		if(is_dir($dir)){
			if($dh = opendir($dir)){
				while(($file = readdir($dh)) !== false){
					if($file == "." || $file == ".."){
						continue;
					}
					if(is_dir($dir . "/" . $file)){
						$this->copydir($dir . "/" . $file, $newdir . "/" . $file);
					}else{
						copy($dir . "/" . $file, $newdir . "/" . $file);
					}
				}
				closedir($dh);
			}
		}
	}

	public function getName(){
		return "";
	}

	public function sendItems(Player $player){

	}

	public function getHoney(){
		return 0;
	}

	public function getGameIdEntry(){
		return -1;
	}

	public function getEntryManager(){
		return $this->entryManager;
	}
}