<?php

namespace Honey\games\SharpFourProtThree;

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
		//ダイヤの剣
		$item = Item::get(276, 0, 1);
		$enchant = Enchantment::getEnchantment(9);
		$enchant->setLevel(4);
		$item->addEnchantment($enchant);
		$enchant = Enchantment::getEnchantment(13);
		$enchant->setLevel(2);
		$item->addEnchantment($enchant);
		$enchant = Enchantment::getEnchantment(17);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->addItem($item);
		//エンダーパール
		$item = Item::get(368, 0, 16);
		$player->getInventory()->addItem($item);
		//金のニンジン
		$item = Item::get(396, 0, 64);
		$player->getInventory()->addItem($item);
		//移動速度上昇のポーション
		$item = Item::get(373, 16, 1);
		$player->getInventory()->addItem($item);
		//耐火のポーション
		$item = Item::get(373, 13, 1);
		$player->getInventory()->addItem($item);
		//スプラッシュ回復のポーション
		for($i=0;$i<28;$i++){
			$item = Item::get(438, 22, 1);
			$player->getInventory()->addItem($item);
		}
		//移動速度上昇のポーション
		for($i=0;$i<3;$i++){
			$item = Item::get(373, 16, 1);
			$player->getInventory()->addItem($item);
		}
		//装備関連
		$item = Item::get(310, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(0, $item);
		$item = Item::get(311, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(1, $item);
		$item = Item::get(312, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(2, $item);
		$item = Item::get(313, 0, 1);
		$enchant = Enchantment::getEnchantment(0);
		$enchant->setLevel(3);
		$item->addEnchantment($enchant);
		$player->getInventory()->setArmorItem(3, $item);
	}

	public function getName(){
		return GameList::NAME_SHARP4PROT3;
	}

	public function getHoney(){
		return 50;
	}

	public function getGameIdEntry(){
		return GameList::GAME_SHARP4PROT3;
	}
}