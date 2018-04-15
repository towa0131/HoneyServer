<?php

namespace Honey\games\Bow;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\item\enchantment\Enchantment;

use pocketmine\plugin\PluginBase;

use Honey\games\GameList;

use Honey\games\normal\Core as NormalCore;

use Honey\games\utils\PotPvP;

class Core extends NormalCore implements PotPvP{

	public function __construct(PluginBase $owner){
		parent::__construct($owner);
	}

	public function sendItems(Player $player){
		//鉄の剣
		$item = Item::get(267, 0, 1);
		$item->setDamage($item->getMaxDurability() - 3);
		$player->getInventory()->addItem($item);
		//弓
		$item = Item::get(261, 0, 1);
		$enchant = Enchantment::getEnchantment(17);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$enchant = Enchantment::getEnchantment(22);
		$enchant->setLevel(1);
		$item->addEnchantment($enchant);
		$player->getInventory()->addItem($item);
		//矢
		$item = Item::get(262, 0, 1);
		$player->getInventory()->addItem($item);
		//金のニンジン
		$item = Item::get(396, 0, 64);
		$player->getInventory()->addItem($item);
		//移動速度上昇のポーション
		$item = Item::get(373, 16, 1);
		$player->getInventory()->addItem($item);
		//装備関連
		$item = Item::get(302, 0, 1);
		$player->getInventory()->setArmorItem(0, $item);
		$item = Item::get(303, 0, 1);
		$player->getInventory()->setArmorItem(1, $item);
		$item = Item::get(304, 0, 1);
		$player->getInventory()->setArmorItem(2, $item);
		$item = Item::get(305, 0, 1);
		$player->getInventory()->setArmorItem(3, $item);
	}

	public function getName(){
		return GameList::NAME_BOW;
	}

	public function getHoney(){
		return 50;
	}

	public function getGameIdEntry(){
		return GameList::GAME_BOW;
	}
}