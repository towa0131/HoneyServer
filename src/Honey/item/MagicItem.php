<?php

namespace Honey\item;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use Honey\account\AccountManager;

abstract class MagicItem extends Item{

	abstract public function transferToItemBox($account);

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		if(AccountManager::hasAccount($player->getXUID())){
			$this->transferToItemBox(AccountManager::getAccount($player));
			return true;
		}
		return false;
	}
}