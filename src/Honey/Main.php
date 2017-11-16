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
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
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
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use pocketmine\entity\Skin;

use pocketmine\utils\Config;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\level\generator\Generator;

use pocketmine\math\Vector3;

use Honey\utils\DB;

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

use Honey\task\RegisterFormTask;
use Honey\task\SendFaceTask;

use Honey\generator\Honey;

use Honey\item\MagicDiamond;

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

	public function onLoad(){
		$this->status = self::STATUS_ENABLE;
	}

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
		$commands = new CommandManager($this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onMain"]), 20);
		$this->getLogger()->info("§a[はにー]§bゲームを初期化しています...");
		$this->time = 0;
		$this->loginTime = [];
		$this->status = self::STATUS_WAIT; //ステータス更新
		$this->waitTime = $this->config->getNested("Game.wait-time");
		$this->playerModule = new PlayerModule();
		Generator::addGenerator(Honey::class, "honey"); //はにージェネレータを登録
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

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$xuid = $player->getXUID();
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
			}else{
				$player->sendMessage("§a[はにー]§bアカウントの読み込みに成功！");
			}
		}
		$this->getServer()->getScheduler()->scheduleAsyncTask(new SendFaceTask($name, $player->getSkin()->getSkinData()));
		$this->playerModule->sendLobbyItem($player);
		if($this->status == self::STATUS_PLAY){
			$items = [
				"\Honey\item\MagicDiamond" => 264
			];
			foreach($player->getInventory()->getContents() as $slot => $item){
				$result = array_search($item->getId(), $items);
				if($result !== false){
					$magicitem = new $result(0, $item->getNamedTag());
					$magicitem->setCount($item->getCount());
					$player->getInventory()->setItem($slot, $magicitem);
				}
			}
		}
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
	}

	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$block = $event->getBlock();
		$x = $block->getX();
		$y = $block->getY();
		$z = $block->getZ();
		$form = new AdminSettingsForm();
		$this->playerModule->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
		$account = AccountManager::getAccount($player);
		$account->addFormHistory($form);
	}

	public function onReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		$player = $event->getPlayer();
		$name = $player->getName();
		$xuid = $player->getXUID();
		$ip = $player->getAddress();
		if($pk instanceof ServerSettingsRequestPacket){
			$account = AccountManager::getAccount($player);
			if($account !== null){ //登録しているときのみ
				$form = new UserSettingsForm($account);
				$pk = new ServerSettingsResponsePacket();
				$pk->formId = FormIds::MENU_USER_SETTINGS;
				$pk->formData = json_encode($form->getFormData());
				$player->dataPacket($pk);
				$form->addFormHistory($account);
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
							$this->playerModule->sendForm($player, $form, FormIds::FORM_REGISTER);
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
							$this->playerModule->sendForm($player, $form, FormIds::FORM_REGISTER);
						}
					}else{ //確認用パスワードがまちがっていた場合
						$form = new RegisterForm();
						$this->playerModule->sendForm($player, $form, FormIds::FORM_REGISTER);
					}
					break;
				case FormIds::FORM_ADMIN_SETTINGS:
					$account = AccountManager::getAccount($player);
					$history = $account->getFormHistory(0);
					switch($history->case){
						case AdminSettingsForm::MENU_MAIN: //ユーザーセレクト画面の表示
							if(is_numeric($rawdata)){
								$form = new AdminSettingsForm(0, null, $rawdata);
								$this->playerModule->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
								$account = AccountManager::getAccount($player);
								$account->addFormHistory($form);
							}
							break;
						case AdminSettingsForm::MENU_USER_SELECT: //ユーザーの設定セレクト画面の表示
							if(is_numeric($rawdata)){
								$target = $history->buttons[(int)$rawdata];
								$account = AccountManager::getAccountByName($target);
								if($account->isOnline()){
									$form = new AdminSettingsForm(2, $account);
									$this->playerModule->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
									$account = AccountManager::getAccount($player);
									$account->addFormHistory($form);
								}else{
									$player->sendMessage("§a[はにー]§4エラー : プレイヤーが見つかりません。");
								}
							}
							break;
						case AdminSettingsForm::MENU_USER_SETTINGS_SELECT: //ユーザー選択画面の表示
							if(is_numeric($rawdata)){
								$ownerAccount = AccountManager::getAccount($player);
								$history = $ownerAccount->getFormHistory(0);
								$account = $history->account;
								switch($rawdata){
									case 0: //アカウント操作画面
										$form = new AdminSettingsForm(AdminSettingsForm::MENU_USER_SETTINGS, $account);
										$this->playerModule->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
										$account = AccountManager::getAccount($player);
										$account->addFormHistory($form);
										break;
									case 1: //XBox垢表示
										$pk = new ShowProfilePacket();
										$pk->xuid = $account->getXuid();
										$player->dataPacket($pk);
										break;
								}
							}
							break;
						case AdminSettingsForm::MENU_USER_SETTINGS: //アカウント操作の更新
							$langList = ["jpn","eng"];
							$viewDistance = ["3", "6", "9", "12", "15", "18"];
							$ownerAccount = AccountManager::getAccount($player);
							$history = $ownerAccount->getFormHistory(0);
							$account = $history->account;
							if(is_numeric($formdata[3])){ //×が押されなかったらアップデート
								AccountManager::updateAccount($account, "playerdata", "honey", $formdata[1]);
								AccountManager::updateAccount($account, "playerdata", "language", $langList[(int)$formdata[2]]);
								AccountManager::updateAccount($account, "settings", "chunk", $viewDistance[(int)$formdata[3]]);
								AccountManager::updateAccount($account, "settings", "floatingtext", (int)$formdata[4]);
								AccountManager::updateAccount($account, "settings", "coordinate", (int)$formdata[5]);
								AccountManager::updateAccount($account, "settings", "temperature", (int)$formdata[6]);
							}
							break;
					}
					break;
				case FormIds::MENU_USER_SETTINGS:
					$account = AccountManager::getAccount($player);
					AccountManager::updateAccount($account, "settings", "floatingtext", (int)$formdata[0]);
					AccountManager::updateAccount($account, "settings", "coordinate", (int)$formdata[1]);
					AccountManager::updateAccount($account, "settings", "temperature", (int)$formdata[2]);
					break;
			}
		}
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