<?php

namespace Honey\form;

use pocketmine\Server;

use Honey\account\AccountManager;

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
	const MENU_USER_SETTINGS = 2;

	/** @var int */
	public $case;
	/** @var Account */
	public $account;
	/** @var string[] */
	public $playernames = [];

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
				break;
			case self::MENU_USER_SELECT: //プレイヤー選択
				$form = new SimpleForm("はにー鯖 | ユーザー設定", "§eプレイヤーを選択してください。");
				foreach(Server::getInstance()->getOnlinePlayers() as $p){
					if(AccountManager::hasAccount($p->getXUID())){
						$name = $p->getName();
						$form->addButton(new Button($name));
						$this->playernames[] = $name;
					}
				}
				break;
			case self::MENU_USER_SETTINGS: //ユーザーの設定
				$account = $this->account;
				$form = new CustomForm("はにー鯖 | ユーザー設定");
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

	/**
	   * @param Account $account
	   */
	public function addFormHistory($account){
		$account->addFormHistory($this);
	}
}