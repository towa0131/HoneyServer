<?php

namespace Honey\entity\AI;

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

use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\MoveEntityPacket;

class EntityAI{

	/**
	   * @param Entity $entity
	   * @param Entity $target
	   */
	public static function lookEntity($entity, $target){
		$pk = new MoveEntityPacket();
		$pk->entityRuntimeId = $entity->getId();
		$xdiff = $target->x - $entity->x;
		$zdiff = $target->z - $entity->z;
		$angle = atan2($zdiff, $xdiff);
		$pk->yaw = (($angle * 180) / M_PI) - 90;
		$ydiff = $target->y - $entity->y;
		$vec = new Vector2($entity->x, $entity->z);
		$dist = $vec->distance($target->x, $target->z);
		$angle = atan2($dist, $ydiff);
		$pk->pitch = (($angle * 180) / M_PI) - 90;
		$pk->position = new Vector3($entity->x, $entity->y, $entity->z);
		$pk->headYaw = $pk->yaw;
		$entity->yaw = $pk->yaw;
		$entity->pitch = $pk->pitch;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
	}
}