<?php

namespace Honey\entity\enemy;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\Location;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\math\Vector3;

class Bee extends Human{

	private $maxhealth = 15;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->setMaxHealth($this->maxhealth);
		$this->setHealth($this->maxhealth);
	}
	
	public function getName() : string{
		return "bee";
	}

	public function getType(){
		return ""; //TODO
	}

	public function attack(EntityDamageEvent $source){
		$damage = $source->getDamage() + 1;
		parent::attack($source);
	}

	public static function spawn($level, $x, $y, $z){
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $x),
				new DoubleTag("", $y),
				new DoubleTag("", $z)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", 0),
				new FloatTag("", 0)
			]),
			"Skin" => new CompoundTag("Skin", [
				new StringTag("geometryData", "TODO")),
				new StringTag("geometryName", "geometry.bee"),
				new StringTag("capeData", ""),
				new StringTag("Data", "TODO"),
				new StringTag("Name", "geometry.bee")
			]),
		]);
		$entity = new Bee($level, $nbt);
		if($entity instanceof Entity){
			$entity->spawnToAll();
			return true;
		}
		return false;
	}
}