<?php

namespace Honey\inventory;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\level\Level;

use pocketmine\math\Vector3;

use pocketmine\plugin\PluginBase;

use pocketmine\inventory\BaseInventory;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;

use pocketmine\network\mcpe\protocol\types\WindowTypes;

use Honey\games\GameList;

use Honey\ItemProvider;

class SelectGameInventory extends BaseInventory{

	private $owner;

	private $x;
	private $y;
	private $z;

	protected $oldBlock;

	public function __construct(PluginBase $owner, Player $player){
		$this->owner = $owner;
		parent::__construct($player, [], 27, "SelectGameInventory");
	}

	public function onOpen(Player $who) : void{
		$x = (int)(round($who->getX()));
		$y = (int)(round($who->getY())) + 3;
		$z = (int)(round($who->getZ()));
		$this->oldBlock = $who->getLevel()->getBlock(new Vector3($x, $y, $z));
		$pk = new UpdateBlockPacket();
		$pk->x = $x;
		$pk->y = $y;
		$pk->z = $z;
		$pk->blockId = 54;
		$pk->blockData = 0;
		$pk->flags = UpdateBlockPacket::FLAG_NONE;
		$who->dataPacket($pk);

		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$c = new CompoundTag("", [
					new StringTag("id", "Chest"),
					new IntTag("x", $x),	
					new IntTag("y", $y),
					new IntTag("z", $z),	
					new StringTag("CustomName", "ゲームを選択")
					]);	
		$nbt->setData($c);
		$pk = new BlockEntityDataPacket();
		$pk->x = $x;
		$pk->y = $y;
		$pk->z = $z;
		$pk->namedtag = $nbt->write(true);
		$who->dataPacket($pk);

		parent::onOpen($who);

		$pk = new ContainerOpenPacket();
		$pk->windowId = $who->getWindowId($this);
		$pk->type = WindowTypes::CONTAINER;
		$pk->x = $x;
		$pk->y = $y;
		$pk->z = $z;
		$who->dataPacket($pk);

		$slot = 0;
		foreach(GameList::getIconList() as $id){
			$item = Item::get($id, 0, 1);
			$gameNames = GameList::getGameNameList();
			$item->setCustomName($gameNames[$slot]);
			ItemProvider::getInstance()->setSelectable($item);
			$this->setItem($slot, $item);
			$slot++;
		}
		$this->sendContents($who);
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);

		$pk = new UpdateBlockPacket();
		$pk->x = $this->oldBlock->x;
		$pk->y = $this->oldBlock->y;
		$pk->z = $this->oldBlock->z;
		$pk->blockId = $this->oldBlock->getID();
		$pk->blockData = $this->oldBlock->getDamage();
		$who->dataPacket($pk);
	}

	public function getName() : string{
		return "SelectGameInventory";
	}

	public function getDefaultSize() : int{
		return 27;
	}

	public function getNetworkType() : int{
		return WindowTypes::CONTAINER;
	}
}