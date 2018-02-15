<?php

namespace Honey;

use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\NBT;

class ItemProvider{

	private static $instance;

	public function __construct(){
		self::$instance = $this;
	}

	public function setUndroppable(Item $item){
		if($item->hasCompoundTag()){
			$tag = $item->getNamedTag();
		}else{
			$tag = new CompoundTag("", []);
		}
		$tag->type = new StringTag("type","Undroppable");
		$item->setNamedTag($tag);
	}

	public function isUndroppable(Item $item){
		$tag = $item->getNamedTag();
		if(isset($tag->type)){
			if($tag->type == "Undroppable"){
				return true;
			}
		}
		return false;
	}

	public function setSelectable(Item $item){
		if($item->hasCompoundTag()){
			$tag = $item->getNamedTag();
		}else{
			$tag = new CompoundTag("", []);
		}
		$tag->type = new StringTag("type","Selectable");
		$item->setNamedTag($tag);
	}

	public function isSelectable(Item $item){
		$tag = $item->getNamedTag();
		if(isset($tag->type)){
			if($tag->type == "Selectable"){
				return true;
			}
		}
		return false;
	}

	public static function getInstance(){
		return self::$instance;
	}
}