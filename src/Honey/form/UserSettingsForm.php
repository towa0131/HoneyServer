<?php

namespace Honey\form;

use Honey\customUI\windows\CustomForm;

use Honey\customUI\elements\Dropdown;
use Honey\customUI\elements\Label;
use Honey\customUI\elements\Toggle;

class UserSettingsForm implements Form{

	/** @var Account */
	public $account;

	/**
	   * @param Account $account
	   */
	public function __construct($account){
		$this->account = $account;
	}

	/**
	   * @return CustomForm
	   */
	public function getFormData(){
		$account = $this->account;
		$form = new CustomForm("はにー鯖");
		$form->addIconUrl("https://honey-mc.net/apple-touch-icon.jpg");
		$form->addElement(new Toggle("採掘時の浮遊文字の表示", $account->isShowFloating()));
		$form->addElement(new Toggle("座標の表示", $account->isShowCoordinate()));
		$form->addElement(new Toggle("気温/天気の表示", $account->isShowTemperature()));
		/*$mine_effects = ["なし"];
		$form->addElement(new Dropdown("採掘時のエフェクト", $mine_effects));*/
		return $form;
	}

	/**
	   * @param Account $account
	   */
	public function addFormHistory($account){
		$account->addFormHistory($this);
	}
}