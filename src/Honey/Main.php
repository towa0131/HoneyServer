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
use pocketmine\item\WrittenBook;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

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
use pocketmine\network\mcpe\protocol\ShowStoreOfferPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\UseItemPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use pocketmine\utils\Config;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

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

use Honey\task\RegisterFormTask;

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
		$this->loginTime = [];
		$this->status = self::STATUS_WAIT; //ステータス更新
		$this->wait_time = $this->config->getNested("Game.wait-time");
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
		$this->sendLobbyItem($player);
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
	}

	public function onReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		$player = $event->getPlayer();
		$name = $player->getName();
		$xuid = $player->getXUID();
		$ip = $player->getAddress();
		if($pk instanceof ServerSettingsRequestPacket){
			$form = new UserSettingsForm();
			$pk = new ServerSettingsResponsePacket();
			$pk->formId = FormIds::MENU_USER_SETTINGS;
			$pk->formData = json_encode($form->getFormData());
			$player->dataPacket($pk);
		}
		if($pk instanceof ModalFormResponsePacket){
			$formid = $pk->formId;
			$formdata = json_decode($pk->formData, true);
			switch($formid){
				case FormIds::FORM_REGISTER:
					if($formdata[1] == $formdata[2]){
						//TODO : pthreadを利用した非同期での処理に変更予定
						$lang = $this->config->getNested("User.default-lang");
						$honey = (int)$this->config->getNested("User.default-honey");
						$skin = bin2hex($player->getSkinData());
						$passwd = $formdata[1];
						for($i=0;$i<=$this->config->getNested("Password.hash-count");$i++){ //ストレッチングを行うことによって、パスワードをより安全に保存できるようにする
							$passwd = hash($this->config->getNested("Password.hash-type"), $passwd);
						}
						$create = AccountManager::registerAccount($xuid, $name, $ip, $passwd, $honey, $lang, $skin, $this->loginTime[$name]);
						if($create){
							//正常にアカウント作成が完了
							$player->sendMessage("§a[はにー]§bアカウントの作成が完了しました。");
							$player->setImmobile(false); //移動できないのを解除
						}else{
							//アカウント作成時に何らかのエラーが発生した
							$player->sendMessage("§a[はにー]§4エラーが発生しました。");
							$form = new RegisterForm();
							$pk = new ModalFormRequestPacket();
							$pk->formId = FormIds::FORM_REGISTER;
							$pk->formData = json_encode($form->getFormData());
							$player->dataPacket($pk);
						}
					}else{ //確認用パスワードがまちがっていた場合
						$form = new RegisterForm();
						$pk = new ModalFormRequestPacket();
						$pk->formId = FormIds::FORM_REGISTER;
						$pk->formData = json_encode($form->getFormData());
						$player->dataPacket($pk);
					}
					break;
			}
		}
	}

	/**
	   * @return $this
	   */
	public static function getInstance(){
		return self::$instance;
	}

	/**
	   * @param Player $player
	   */
	public function sendLobbyItem(Player $player){
		//本配布
		$path = __DIR__ . "/images/";
		$imgdata = file_get_contents($path . "logo.png");
		$photo = new PhotoTransferPacket;
		$photo->photoName = $path . "logo.png";
		$photo->photoData = $imgdata;
		$photo->bookId = "0";
		$player->dataPacket($photo);
		$book = Item::get(Item::WRITTEN_BOOK, 0, 1);
		$nbt = new CompoundTag("", [
			new StringTag("title", "§aサーバーの情報"),
			new StringTag("author", "はにい"),
			new IntTag("generation", WrittenBook::GENERATION_ORIGINAL),
			new IntTag("id", 0),
			new ListTag("pages", [])
		]);
		$book->setNamedTag($nbt);
		//なぜかエラーがでる...
		//$book->setTitle("§aサーバーの情報");
		//$book->setAuthor("はにい");
		//$book->setGeneration(WrittenBook::GENERATION_ORIGINAL);
		for($i=0;$i<20;$i++){
			$book->addPage($i);
		}
		$book->setbookId(0);
		$book->setPageImage(0, $path . "logo.png");
		$player->getInventory()->addItem($book);
	}
}