<?php

namespace Honey\form;

use Honey\customUI\windows\CustomForm;

use Honey\customUI\elements\Dropdown;
use Honey\customUI\elements\Label;
use Honey\customUI\elements\Toggle;

class UserSettingsForm implements Form{

	public function getFormData(){
		$form = new CustomForm("はにー鯖");
		$form->addIconUrl("http://108.61.182.170/apple-touch-icon.jpg");
		$form->addElement(new Label("§e※現在この機能は開発中です。(設定は反映されません)"));
		$form->addElement(new Toggle("採掘時の浮遊文字の表示", true));
		$form->addElement(new Toggle("自分の座標の表示", true));
		$form->addElement(new Toggle("気温/天気の表示", true));
		$mine_effects = ["なし"];
		$form->addElement(new Dropdown("採掘時のエフェクト", $mine_effects));
		return $form;
	}
}