<?php

namespace Honey\games\SharpFourProtThree;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\level\Position;

use pocketmine\item\Item;

use pocketmine\item\enchantment\Enchantment;

use pocketmine\Player;
use pocketmine\Server;

use Honey\Main;

use Honey\PlayerModule;
use Honey\ItemProvider;

use Honey\account\AccountManager;

class Core implements Listener{

	/** @var $this */
	private static $instance;
	/** @var PluginBase */
	private $owner;

	public function __construct(PluginBase $owner){
		$this->owner = $owner;
		self::$instance = $this;
		new EntryManager();
		new GameIdManager();
	}

	public function onEntry(Player $player){
		$player->sendMessage("§a[はにー]§bSharp4Prot3にエントリーしました。");
	}

	public function onDeath(PlayerDeathEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(EntryManager::getInstance()->isEntryPlayer($player)){
			$gameId = GameIdManager::getInstance()->getGameIdByPlayer($player);
			$players = GameIdManager::getInstance()->getPlayersByGameId($gameId);
			$event->setDeathMessage("");
			if($players[0] == $name){
				$this->endGame(Server::getInstance()->getPlayer($players[1]), Server::getInstance()->getPlayer($players[0]));
				return true;
			}
			if($players[1] == $name){
				$this->endGame(Server::getInstance()->getPlayer($players[0]), Server::getInstance()->getPlayer($players[1]));
				return true;
			}
		}
		return false;
	}

	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		if(GameIdManager::getInstance()->hasPlayer($player)){
			$event->setQuitMessage("");
			$gameId = GameIdManager::getInstance()->getGameIdByPlayer($player);
			$players = GameIdManager::getInstance()->getPlayersByGameId($gameId);
			if($players[0] === $player->getName()){
				$this->endGame(Server::getInstance()->getPlayer($players[1]), $player, false, true);
			}else{
				$this->endGame(Server::getInstance()->getPlayer($players[0]), $player, false, true);
			}
		}
		if(EntryManager::getInstance()->isEntryPlayer($player)){
			EntryManager::getInstance()->removeEntryPlayer($player);
		}
	}

	public function startGame(Player $playerA, Player $playerB){
		$playerA->sendMessage("§a[はにー]§bまもなく試合が始まります。");
		$playerB->sendMessage("§a[はにー]§bまもなく試合が始まります。");
		$playerA->getInventory()->clearAll();
		$playerB->getInventory()->clearAll();
		//のちのち変更予定
		if(Server::getInstance()->isLevelLoaded("1vs1")){
			$level = Server::getInstance()->getLevelByName("1vs1");
			$level->setAutoSave(false);
			Server::getInstance()->unloadLevel($level);
		}
		Server::getInstance()->loadLevel("1vs1");
		$task = new TeleportToPvPWorldTask(Main::getInstance(), $this, $playerA, $playerB, new Position(289.9, 8, 249.9, Server::getInstance()->getLevelByName("1vs1")), new Position(290.0, 8, 163.9, Server::getInstance()->getLevelByName("1vs1")));
		Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
	}

	public function endGame($winner, $loser, $timeUp = false, $quit = false){
		$defaultLevel = Server::getInstance()->getLevelByName(Main::getInstance()->config->getNested("Level.default-world"));
		$taskId = GameIdManager::getInstance()->getTaskIdByPlayer($winner);
		EntryManager::getInstance()->removeEntryPlayer($winner);
		GameIdManager::getInstance()->removeGameId(GameIdManager::getInstance()->getGameIdByPlayer($winner));
		Server::getInstance()->getScheduler()->cancelTask($taskId);
		$log = "§a[はにー]§bゲームが終了しました。";
		$winner->sendMessage($log);
		if(!$quit){
			$loser->sendMessage($log);
		}
		if(!$timeUp){
			$log = "§c=== Sharp4Prot3 ===" .
				PHP_EOL .
				" §4Winner §f- " . $winner->getName() .
				PHP_EOL .
				PHP_EOL .
				" §1Loser §f- " . $loser->getName() .
				PHP_EOL .
				"§c==================";
			$winner->sendMessage($log);
			$honey = 50;
			$account = AccountManager::getAccount($winner);
			if($account !== null){
				$account->addHoney($honey);
				$winner->sendMessage("§a[はにー]§e" . $honey . "はにい§a入手しました。");
			}else{
				$winner->sendMessage("§a[はにー]§cエラーが発生しました。");
			}
			$task = new TeleportToLobbyTask(Main::getInstance(), $winner, $defaultLevel->getSafeSpawn());
			Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
			if(!$quit){
				$loser->sendMessage($log);
				$task = new TeleportToLobbyTask(Main::getInstance(), $loser, $defaultLevel->getSafeSpawn());
				Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
			}
			return true;
		}
		$log = "§c=== Sharp4Prot3 ===" .
			PHP_EOL .
			"§a          Draw" .
			PHP_EOL .
			"§c==================";
		$winner->sendMessage($log);
		$task = new TeleportToLobbyTask(Main::getInstance(), $winner, $defaultLevel->getSafeSpawn());
		Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
		if(!$quit){
			$loser->sendMessage($log);
			$task = new TeleportToLobbyTask(Main::getInstance(), $loser, $defaultLevel->getSafeSpawn());
			Server::getInstance()->getScheduler()->scheduleDelayedTask($task, 20 * 5);
		}
		return true;
	}

	public function sendItems(Player $player){
		//ダイヤの剣
		$item = Item::get(276, 0, 1);
		$enchant = Enchantment::getEnchantment(9);
		$enchant->setLevel(4);
		$item->addEnchantment($enchant);
		$player->getInventory()->addItem($item);
		//エンダーパール
		$item = Item::get(368, 0, 16);
		$player->getInventory()->addItem($item);
		//ステーキ
		$item = Item::get(364, 0, 64);
		$player->getInventory()->addItem($item);
		//攻撃力上昇のポーション
		$item = Item::get(373, 15, 1);
		$player->getInventory()->addItem($item);
		//移動速度上昇のポーション
		$item = Item::get(373, 32, 1);
		$player->getInventory()->addItem($item);
		//スプラッシュ回復のポーション
		for($i=0;$i<31;$i++){
			$item = Item::get(438, 21, 1);
			$player->getInventory()->addItem($item);
		}
		//装備関連
		$item = Item::get(310, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(0, $item);
		$item = Item::get(311, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(1, $item);
		$item = Item::get(312, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(2, $item);
		$item = Item::get(313, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(3, $item);
	}

	public function entryGame(Player $player){
		$player->getInventory()->clearAll();
		$item = Item::get(355, 14, 1);
		ItemProvider::getInstance()->setUndroppable($item);
		$item->setCustomName("§cエントリーをキャンセル");
		$player->getInventory()->setItem(20, $item);
		$player->getInventory()->setHotbarSlotIndex(4, 20);
		EntryManager::getInstance()->addEntryPlayer($player);
	}

	public function cancelEntryGame(Player $player){
		$player->sendMessage("§a[はにー]§cエントリーをキャンセルしました。");
		$player->getInventory()->clearAll();
		PlayerModule::getInstance()->sendLobbyItem($player);
		EntryManager::getInstance()->removeEntryPlayer($player);
	}

	public function isEntryGame(Player $player){
		$entry = EntryManager::getInstance()->isEntryPlayer($player);
		return $entry;
	}

	public static function getInstance(){
		return self::$instance;
	}
}