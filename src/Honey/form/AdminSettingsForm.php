<?php

namespace Honey\form;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\network\mcpe\protocol\ShowProfilePacket;

use Honey\account\Account;
use Honey\account\AccountManager;

use Honey\FormIds;
use Honey\PlayerModule;

use Honey\customUI\windows\SimpleForm;
use Honey\customUI\windows\CustomForm;

use Honey\customUI\elements\Input;
use Honey\customUI\elements\Button;
use Honey\customUI\elements\Dropdown;
use Honey\customUI\elements\StepSlider;
use Honey\customUI\elements\Label;
use Honey\customUI\elements\Toggle;


class AdminSettingsForm implements Form{

	const MENU_MAIN = 0;
	const MENU_USER_SELECT = 1;
	const MENU_USER_SETTINGS_SELECT = 2;
	const MENU_USER_SETTINGS = 3;
	const MENU_SERVER_SETTINGS = 4;

	/** @var int */
	protected $case;
	/** @var Account */
	protected $account;
	/** @var string[] */
	private $buttons = [];

	/**
	   * @param int $case
	   * @param Account $account
	   * @param int $type
	   */
	public function __construct($case = 0, $account = null, $type = null){
		$this->case = $case;
		$this->account = $account;
		if($type !== null){
			switch($type){ //メインメニューのボタンを押したとき
				case 0:
					$this->case = self::MENU_USER_SELECT;
					break;
				default:
					$this->case = self::MENU_MAIN;
					break;
			}
		}
	}

	/**
	   * @return SimpleForm|CustomForm
	   */
	public function getFormData(){
		switch($this->case){
			case self::MENU_MAIN:
				$form = new SimpleForm("はにー鯖 | 管理者メニュー", "");
				$form->addButton(new Button("他のプレイヤーの設定"));
				$form->addButton(new Button("サーバー設定"));
				break;
			case self::MENU_USER_SELECT: //プレイヤー選択
				$form = new SimpleForm("はにー鯖 | ユーザー設定", "§eプレイヤーを選択してください。");
				foreach(Server::getInstance()->getOnlinePlayers() as $p){
					if(AccountManager::hasAccount($p->getXuid())){
						$name = $p->getName();
						$form->addButton(new Button($name));
						$this->buttons[] = $name;
					}
				}
				break;
			case self::MENU_USER_SETTINGS_SELECT:
				$account = $this->account;
				$form = new SimpleForm("はにー鯖 | ユーザー設定 > " . $account->getName(), "");
				$form->addButton(new Button("ユーザー設定"));
				$form->addButton(new Button("XBoxアカウントの表示"));
				break;
			case self::MENU_USER_SETTINGS: //ユーザーの設定
				$account = $this->account;
				$form = new CustomForm("はにー鯖 | ユーザー設定 > " . $account->getName());
				$form->addElement(new Label($account->getName() . "のユーザー設定"));
				$form->addElement(new Input("所持はにい: ",$account->getHoney(), $account->getHoney()));
				$drop = new Dropdown("言語設定: ", ["日本語", "English(US)"]);
				switch($account->getLanguage()){
					case "jpn":
						$drop->setOptionAsDefault("日本語");
						break;
					case "eng":
						$drop->setOptionAsDefault("English(US)");
						break;
				}
				$form->addElement($drop);
				$form->addElement(new Label("====== MineCrash ======"));
				$step = new StepSlider("表示チャンク", ["3", "6", "9", "12", "15", "18"]);
				$step->setStepAsDefault($account->getViewDistance());
				$form->addElement($step);
				$form->addElement(new Toggle("採掘時の浮遊文字の表示", $account->isShowFloating()));
				$form->addElement(new Toggle("座標の表示", $account->isShowCoordinate()));
				$form->addElement(new Toggle("気温/天気の表示", $account->isShowTemperature()));
				break;
		}
		return $form;
	}

	public function onSendForm(string $formData, int $formId, Player $player, Form $historyForm){
		$formJson = json_decode($formData, true);
		$case = $historyForm->getCase();
		switch($case){
			case self::MENU_MAIN: //メインメニュー
				if(is_numeric($formData)){
					switch($formData){
						case 0:
							$form = new AdminSettingsForm(self::MENU_USER_SELECT, null, $formData);
							PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
							break;
						case 1:
							$form = new AdminSettingsForm(self::MENU_SERVER_SETTINGS, null, $formData);
							PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
							break;
					}
				}
			break;
			case self::MENU_USER_SELECT: //ユーザーの設定セレクト画面の表示
				if(is_numeric($formData)){
					$target = $historyForm->getButtonData((int)$formData);
					$account = AccountManager::getAccountByName($target);
					if($account !== null){
						if($account->isOnline()){
							$form = new AdminSettingsForm(self::MENU_USER_SETTINGS_SELECT, $account);
							PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
						}else{
							$player->sendMessage("§a[はにー]§4エラー : プレイヤーが見つかりません。");
						}
					}else{
						$player->sendMessage("§a[はにー]§4エラー : アカウントが存在しません。");
					}
				}
			break;
			case self::MENU_USER_SETTINGS_SELECT: //ユーザー選択画面の表示
				if(is_numeric($formData)){
					$ownerAccount = AccountManager::getAccount($player);
					$history = $ownerAccount->getFormHistory(0);
					$targetAccount = $history->getAccount();
					switch($formData){
						case 0: //アカウント操作画面
							$form = new AdminSettingsForm(AdminSettingsForm::MENU_USER_SETTINGS, $targetAccount);
							PlayerModule::getInstance()->sendForm($player, $form, FormIds::FORM_ADMIN_SETTINGS);
							break;
						case 1: //XBoxアカウント表示
							$pk = new ShowProfilePacket();
							$pk->xuid = $targetAccount->getXuid();
							$player->dataPacket($pk);
							break;
					}
				}
				break;
			case self::MENU_USER_SETTINGS: //アカウント操作の更新
				$langList = ["jpn","eng"];
				$viewDistance = ["3", "6", "9", "12", "15", "18"];
				$ownerAccount = AccountManager::getAccount($player);
				$history = $ownerAccount->getFormHistory(0);
				$account = $history->getAccount();
				if(is_numeric($formJson[4])){ //×が押されなかったらアップデート
					AccountManager::updateAccount($account, "playerdata", "honey", $formJson[1]);
					AccountManager::updateAccount($account, "playerdata", "language", $langList[(int)$formJson[2]]);
					AccountManager::updateAccount($account, "minecrash", "chunk", $viewDistance[(int)$formJson[4]]);
					AccountManager::updateAccount($account, "minecrash", "floatingtext", (int)$formJson[5]);
					AccountManager::updateAccount($account, "minecrash", "coordinate", (int)$formJson[6]);
					AccountManager::updateAccount($account, "minecrash", "temperature", (int)$formJson[7]);
				}
				break;
		}
	}

	/**
	   * @param Account $account
	   */
	public function addFormHistory($account){
		$account->addFormHistory($this);
	}

	public function getCase(){
		return $this->case;
	}

	public function setCase(int $case){
		$this->case = $case;
	}

	public function getAccount(){
		return $this->account;
	}

	public function setAccount(Account $account){
		$this->account = $account;
	}

	public function getButtons(){
		return $this->buttons;
	}

	public function getButtonData(int $index){
		if(isset($this->buttons[$index])){
			return $this->buttons[$index];
		}
		return null;
	}
}