<?php

namespace Honey;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\scheduler\CallbackTask;
use pocketmine\scheduler\PluginTask;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\item\Item;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

use pocketmine\event\block\BlockBreakEvent;

use pocketmine\utils\Config;

use Honey\utils\DB;
use Honey\account\AccountManager;

class Main extends PluginBase implements Listener{

	const STATUS_WAIT = 0; //待機時間(ゲーム人数が揃っていない状態)
	const STATUS_LOAD = 1; //ロード時間(ゲーム人数が揃ってゲームが開始されるまでの時間)
	const STATUS_PLAY = 2; //ゲームプレイ時間(ゲームが行われている状態)

	/** @var $this */
	private static $instance;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("§a[はにー]§bプラグインを読み込んでいます...");
		if(!file_exists($this->getDataFolder())){
			$this->getLogger()->info("§a[はにー]§bコンフィグファイルを生成しています...");
			mkdir($this->getDataFolder() , 0777);
			$this->saveDefaultConfig();
		}
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		DB::setConfig($this->config->getNested("DB.address"),
			$this->config->getNested("DB.user"),
			$this->config->getNested("DB.password"),
			$this->config->getNested("DB.database"),
			$this->config->getNested("DB.timeout"));
		self::$instance = $this;
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onMain"]), 20);
		$this->getLogger()->info("§a[はにー]§bゲームを初期化しています...");
		$this->time = 0;
		$this->status = self::STATUS_WAIT; //ステータス更新
		$this->wait_time = $this->config->getNested("Game.wait-time");
	}

	public function onMain(){
		var_dump(AccountManager::getAccountByXuid("1234"));
		switch($this->status){
			case self::STATUS_WAIT:
				if(count($this->getServer()->getOnlinePlayers()) >= $this->config->getNested("Game.start-players")){
					//ゲーム開始人数が揃ったらゲームロード状態にステータスが更新
					$this->status = self::STATUS_LOAD;
				}
				break;
			case self::STATUS_LOAD:
				$this->wait_time--;
				switch($this->wait_time){ //ゲームロード時間が特定の時間になったときの処理
					case $this->config->getNested("Game.wait-time"):
						//人数が揃ったことをプレイヤーに通知
						foreach($this->getServer()->getOnlinePlayers() as $p){
							// TODO
						}
						break;
				}
				break;
			case self::STATUS_PLAY:
				// TODO
				break;
		}
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
	}

	/**
	   * @return $this
	   */
	public static function getInstance(){
		return self::$instance;
	}
}