<?php

namespace Honey\plugin;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use Honey\Main;

use Honey\account\Account;
use Honey\account\AccountManager;

use Honey\form\Form;

abstract class HoneyPluginBase{

	const TYPE_PLAYER = 0;
	const TYPE_NAME = 1;
	const TYPE_XUID = 2;

	public function onEnable(){}

	public function onDisable(){}

	/**
	   * @param Account $account
	   * @param Form $form
	   */
	public function onSendForm(Account $account, Form $form){}

	/**
	   * @param string $xuid
	   *
	   * @return Account|null
	   */
	public function getAccount($key, $type = self::TYPE_PLAYER){
		switch($type){
			case self::PLAYER:
				$result = AccountManager::getAccount($key);
				break;
			case self::NAME:
				$result = AccountManager::getAccountByName($key);
				break;
			case self::TYPE_XUID:
				$result = AccountManager::getAccountByXuid($key);
				break;
			default:
				return null;
		}
		return $result;
	}

	/**
	   * @return Main
	   */
	public function getMain(){
		return Main::getInstance();
	}
}