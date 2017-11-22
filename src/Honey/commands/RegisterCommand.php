<?php

namespace Honey\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use Honey\account\AccountManager;

use Honey\FormIds;

use Honey\form\RegisterForm;

class RegisterCommand extends PluginCommand{

	/**
	   * @param PluginBase
	   */
	public function __construct(PluginBase $owner){
		parent::__construct("register", $owner);
		$this->setDescription("登録フォームを呼び出します。");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$xuid = $sender->getXuid();
		if(!AccountManager::hasAccount($xuid)){
			$form = new RegisterForm();
			$pk = new ModalFormRequestPacket();
			$pk->formId = FormIds::FORM_REGISTER;
			$pk->formData = json_encode($form->getFormData());
			$sender->dataPacket($pk);
			return true;
		}
		$sender->sendMessage("§a[はにい]§cあなたは既に登録してます。");
		return false;
	}

}