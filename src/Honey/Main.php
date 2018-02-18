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
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\event\inventory\InventoryTransactionEvent;

use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PhotoTransferPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\network\mcpe\protocol\ShowProfilePacket;
use pocketmine\network\mcpe\protocol\ShowStoreOfferPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\UseItemPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use pocketmine\network\mcpe\protocol\types\ContainerIds;

use pocketmine\entity\Skin;

use pocketmine\utils\Config;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\level\generator\Generator;

use pocketmine\math\Vector3;

use Honey\utils\DB;
use Honey\utils\Utils;
use Honey\utils\ErrNo;

use Honey\account\AccountManager;

use Honey\customUI\windows\ModalWindow;
use Honey\customUI\windows\CustomForm;
use Honey\customUI\windows\SimpleForm;

use Honey\customUI\elements\Button;
use Honey\customUI\elements\Dropdown;
use Honey\customUI\elements\Image;
use Honey\customUI\elements\Input;
use Honey\customUI\elements\Label;
use Honey\customUI\elements\Slider;
use Honey\customUI\elements\StepSlider;
use Honey\customUI\elements\Toggle;

use Honey\form\RegisterForm;
use Honey\form\UserSettingsForm;
use Honey\form\AdminSettingsForm;

use Honey\inventory\SelectGameInventory;

use Honey\task\RegisterFormTask;
use Honey\task\SendFaceTask;

use Honey\generator\Honey;

use Honey\event\system\SystemErrorEvent;

use Honey\item\MagicItem;

use Honey\games\GameList;

use Honey\games\SharpFourProtThree\Core as SharpFourProtThreeCore;
use Honey\games\SharpTwoProtTwo\Core as SharpTwoProtTwoCore;

use Honey\plugin\HoneyPluginLoader;

class Main extends PluginBase implements Listener{

	const VERSION = "1.0.0";
	const CODENAME = "Glass Rabbit";

	const STATUS_ENABLE = 0; //プラグインのロード中
	const STATUS_WAIT = 1; //待機時間(ゲーム人数が揃っていない状態)
	const STATUS_LOAD = 2; //ロード時間(ゲーム人数が揃ってゲームが開始されるまでの時間)
	const STATUS_PLAY = 3; //ゲームプレイ時間(ゲームが行われている状態)
	const STATUS_END = 4; //ゲーム終了後
	const STATUS_DISABLE = 5; //プラグインのアンロード中

	/** @var $this */
	private static $instance;
	/** @var HoneyPluginLoader */
	private $pluginLoader;

	public function onLoad(){
		$this->status = self::STATUS_ENABLE;
	}

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getPluginManager()->registerEvents(new SharpFourProtThreeCore($this),$this);
		$this->getServer()->getPluginManager()->registerEvents(new SharpTwoProtTwoCore($this),$this);
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
		$this->pluginLoader = new HoneyPluginLoader();
		$commands = new CommandManager($this);
		$itemProvider = new ItemProvider();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onMain"]), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onDBRefresh"]), 300);
		$this->getLogger()->info("§a[はにー]§bゲームを初期化しています...");
		$this->time = 0;
		$this->loginTime = [];
		$this->status = self::STATUS_WAIT; //ステータス更新
		$this->waitTime = $this->config->getNested("Game.wait-time");
		new PlayerModule();
		Generator::addGenerator(Honey::class, "honey"); //はにージェネレータを登録
		//$this->pluginLoader->loadPlugin($this->getServer()->getPluginPath() . "HoneyMusic_v1.0.0");
	}

	public function onMain(){
		switch($this->status){
			case self::STATUS_WAIT:
				if(count($this->getServer()->getOnlinePlayers()) >= $this->config->getNested("Game.start-players")){
					//ゲーム開始人数が揃ったらゲームロード状態にステータスが更新
					$this->status = self::STATUS_LOAD;
				}
				break;
			case self::STATUS_LOAD:
				$this->waitTime--;
				switch($this->waitTime){ //ゲームロード時間が特定の時間になったときの処理
					case $this->config->getNested("Game.wait-time"):
						//人数が揃ったことをプレイヤーに通知
						foreach($this->getServer()->getOnlinePlayers() as $p){
							
						}
						break;
				}
				break;
			case self::STATUS_PLAY:
				// TODO
				break;
		}
	}

	public function onPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$xuid = $player->getXuid();
		if($xuid === ""){ //XBoxアカウント認証回避対策
			$player->setKickMessage("§4Error #001\n§cXBoxへログインをしてください。");
			$event->setCancelled(true);
			Utils::callError(ErrNo::ERRNO_001);
			return false;
		}
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$xuid = $player->getXuid();
		$this->loginTime[$name] = date("Y-m-d H:i:s");
		if(!AccountManager::hasAccount($xuid)){
			//アカウント登録がされてなければアカウント登録フォームを送信する
			//そのまま送信するとバグる為、タイミングをずらして送信する
			$task = new RegisterFormTask($this, $player);
			$this->getServer()->getScheduler()->scheduleDelayedTask($task, 15);
			$player->sendMessage("§a[はにー]§cもし、登録フォームが出なかったら/registerと入力してください。");
			$player->setImmobile(true); //移動できなくする(登録回避の回避)
		}else{
			$player->sendMessage("§a[はにー]§bアカウントを読み込んでいます...");
			$account = AccountManager::getAccount($player);
			if($account == null){
				$player->sendMessage("§a[はにー]§4エラーが発生しました。再度ログインをお願いします。");
				Utils::callError(ErrNo::ERRNO_003);
			}else{
				$player->sendMessage("§a[はにー]§bアカウントの読み込みに成功！");
			}
		}
		$this->getServer()->getScheduler()->scheduleAsyncTask(new SendFaceTask($name, $player->getSkin()->getSkinData()));
		if($this->status == self::STATUS_PLAY){
			$items = [
				"\Honey\item\MagicCoal" => [263, 0],
				"\Honey\item\MagicDiamond" => [264, 0],
				"\Honey\item\MagicIron" => [265, 0],
				"\Honey\item\MagicGold" => [266, 0],
				"\Honey\item\MagicRedstone" => [331, 0],
				"\Honey\item\MagicLapisLazuli" => [351, 4],
				"\Honey\item\MagicEmerald" => [388, 0]
			];
			foreach($player->getInventory()->getContents() as $slot => $item){
				foreach($items as $key => $value){
					if($value[0] === $item->getId() && $value[1] === $item->getDamage()){
						$magicitem = new $key($value[1], $item->getNamedTag());
						$magicitem->setCount($item->getCount());
						ItemProvider::getInstance()->setUndroppable($magicitem);
						$player->getInventory()->setItem($slot, $magicitem);
						break;
					}
				}
			}
		}
		$player->getInventory()->clearAll();
		$player->teleport($this->getServer()->getLevelByName($this->config->getNested("Level.default-world"))->getSafeSpawn());
		$player->setGamemode(2);
		$player->setHealth(20);
		$player->setFood(20);
		PlayerModule::getInstance()->sendLobbyItem($player);
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!$player->isOp()){
			$event->setCancelled();
		}
	}

	public function onDamage(EntityDamageEvent $event){
		$player = $event->getEntity();
		$name = $player->getName();
		$level = $player->getLevel();
		if($level->getFolderName() == $this->config->getNested("Level.default-world")){
			$event->setCancelled();
		}
		if($level->getFolderName() == $this->config->getNested("Level.wait-world")){
			$event->setCancelled();
		}
	}

	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$block = $event->getBlock();
		$x = $block->getX();
		$y = $block->getY();
		$z = $block->getZ();
		$item = $player->getInventory()->getItemInHand();
		$level = $player->getLevel();
		if($level->getFolderName() == $this->config->getNested("Level.default-world")){
			switch($item->getId()){
				case 276: //ダイヤの剣
					$inventory = new SelectGameInventory($this, $player);
					$player->addWindow($inventory);
					break;
				case 355: //ベッド(エントリーキャンセル用)
					if(SharpFourProtThreeCore::getInstance()->isEntryGame($player)){
						SharpFourProtThreeCore::getInstance()->cancelEntryGame($player);
					}
					if(SharpTwoProtTwoCore::getInstance()->isEntryGame($player)){
						SharpTwoProtTwoCore::getInstance()->cancelEntryGame($player);
					}
					break;
				case 369:
					if($player->isOp()){
						$form = new AdminSettingsForm();
						PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
						$account = AccountManager::getAccount($player);
					}
					break;
			}
		}
	}

	public function onReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		$player = $event->getPlayer();
		$name = $player->getName();
		$xuid = $player->getXuid();
		$ip = $player->getAddress();
		if($pk instanceof ServerSettingsRequestPacket){
			$account = AccountManager::getAccount($player);
			if($account !== null){ //登録しているときのみ
				$form = new UserSettingsForm($account);
				$pk = new ServerSettingsResponsePacket();
				$pk->formId = FormIds::MENU_USER_SETTINGS;
				$pk->formData = json_encode($form->getFormData());
				$player->dataPacket($pk);
				$account->addFormHistory($form);
			}
		}
		if($pk instanceof ModalFormResponsePacket){
			$formid = $pk->formId;
			$rawdata = trim($pk->formData); //trimする理由はnullが送られてきたときにnull検知に引っ掛からないため(多分"NULL "になってる)
			$formdata = json_decode($rawdata, true);
			switch($formid){
				case FormIds::FORM_REGISTER:
					if($formdata[1] == $formdata[2]){
						//TODO : pthreadを利用した非同期での処理に変更予定
						$lang = $this->config->getNested("User.default-lang");
						$honey = (int)$this->config->getNested("User.default-honey");
						$skin = bin2hex($player->getSkin()->getSkinData());
						$passwd = trim($formdata[1]);
						if($passwd == null){ //×対策
							$form = new RegisterForm();
							PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_REGISTER);
							return;
						}
						for($i=0;$i<=$this->config->getNested("Password.hash-count");$i++){ //ストレッチングを行うことによって、パスワードをより安全に保存できるようにする
							$passwd = hash($this->config->getNested("Password.hash-type"), $passwd);
						}
						$create = AccountManager::registerAccount($xuid, $name, $ip, $passwd, $honey, $lang, $skin, $this->loginTime[$name]);
						if($create){
							//正常にアカウント作成が完了
							$player->sendMessage("§a[はにー]§bアカウントの作成が完了しました。");
							$player->setImmobile(false); //移動できないのを解除
							$account = AccountManager::getAccount($player);
							$account->addFormHistory(new RegisterForm()); //RegisterFormのインスタンスがないため新たに作成
						}else{
							//アカウント作成時に何らかのエラーが発生した
							$player->sendMessage("§a[はにー]§4エラーが発生しました。");
							$form = new RegisterForm();
							PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_REGISTER);
							Utils::callError(ErrNo::ERRNO_004);
						}
					}else{ //確認用パスワードがまちがっていた場合
						$form = new RegisterForm();
						PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_REGISTER);
					}
					break;
				case FormIds::FORM_ADMIN_SETTINGS:
					$account = AccountManager::getAccount($player);
					$history = $account->getFormHistory(0);
					$form = new AdminSettingsForm();
					$form->onSendForm($rawdata, $formid, $player, $history);
					break;
				case FormIds::MENU_USER_SETTINGS:
					$account = AccountManager::getAccount($player);
					AccountManager::updateAccount($account, "minecrash", "floatingtext", (int)$formdata[0]);
					AccountManager::updateAccount($account, "minecrash", "coordinate", (int)$formdata[1]);
					AccountManager::updateAccount($account, "minecrash", "temperature", (int)$formdata[2]);
					break;
			}
		}

		if($pk instanceof InventoryTransactionPacket){
			$type = $pk->transactionType;
			if($type === InventoryTransactionPacket::TYPE_NORMAL || $type === InventoryTransactionPacket::TYPE_MISMATCH){
				$actions = $pk->actions;
				$item = $actions[0]->newItem;
				if(ItemProvider::getInstance()->isSelectable($item)){
					switch($item->getId()){
						case GameList::ICON_MINECRASH:
							$event->setCancelled();
							break;
						case GameList::ICON_SHARP4PROT3:
							$event->setCancelled();
							if(SharpFourProtThreeCore::getInstance()->isEntryGame($player) || SharpTwoProtTwoCore::getInstance()->isEntryGame($player)){
								return;
							}
							SharpFourProtThreeCore::getInstance()->entryGame($player);
							$player->removeAllWindows();
							break;
						case GameList::ICON_SHARP2PROT2:
							$event->setCancelled();
							if(SharpFourProtThreeCore::getInstance()->isEntryGame($player) || SharpTwoProtTwoCore::getInstance()->isEntryGame($player)){
								return;
							}
							SharpTwoProtTwoCore::getInstance()->entryGame($player);
							$player->removeAllWindows();
							break;
						case GameList::ICON_FFA:
							$event->setCancelled();
							break;
					}
				}
			}
		}
	}

	public function onDropItem(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		$item = $event->getItem();
		$name = $player->getName();
		$level = $player->getLevel();
		if($level->getFolderName() == $this->config->getNested("Level.default-world")){
			$event->setCancelled();
		}
		if(ItemProvider::getInstance()->isUndroppable($item)){
			$event->setCancelled();
		}
	}

	public function onError(SystemErrorEvent $event){
		$errno = $event->getErrNo();
		$errmsg = $event->getErrMsg();
	}

	public function onDBRefresh(){
		DB::refreshConnect();
	}

	public function onDisable(){
		if(DB::isConnect()){
			DB::resetConnect();
		}
		$this->status = self::STATUS_DISABLE;
	}

	/**
	   * @return $this
	   */
	public static function getInstance(){
		return self::$instance;
	}
}