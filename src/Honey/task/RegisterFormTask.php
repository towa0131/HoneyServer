<?php

namespace Honey\task;

use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\PluginTask;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use pocketmine\Player;

use Honey\FormIds;

use Honey\form\RegisterForm;

class RegisterFormTask extends PluginTask{

	/**
	   * @param PluginBase $owner
	   * @param Player $player
	   */
	public function __construct(PluginBase $owner, Player $player){
		parent::__construct($owner);
		$this->player = $player;
	}

	public function onRun(int $currentTick){
		$form = new RegisterForm();
		$pk = new ModalFormRequestPacket();
		$pk->formId = FormIds::FORM_REGISTER;
		$pk->formData = json_encode($form->getFormData());
		$this->player->dataPacket($pk);
	}
}