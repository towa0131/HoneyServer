<?php

namespace Honey\inventory;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\plugin\PluginBase;

use Honey\ItemProvider;

use Honey\games\GameList;

class DuelInventory extends SelectGameInventory{

	private $owner;

	protected $sender;
	protected $target;

	public function __construct(PluginBase $owner, Player $sender, Player $target){
		$this->owner = $owner;
		$this->sender = $sender;
		$this->target = $target;
		parent::__construct($owner, $sender);
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);
		$this->clearAll();
		$slot = 0;
		foreach(GameList::getIconList() as $id){
			$item = Item::get($id, 0, 1);
			$gameNames = GameList::getGameNameList();
			$item->setCustomName($gameNames[$slot]);
			ItemProvider::getInstance()->setDuelable($item, $this->sender, $this->target);
			$this->setItem($slot, $item);
			$slot++;
		}
		$this->sendContents($who);
	}
}