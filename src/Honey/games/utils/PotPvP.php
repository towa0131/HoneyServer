<?php

namespace Honey\games\utils;

use pocketmine\Player;

interface PotPvP{

	public function getName();

	public function sendItems(Player $player);

	public function getHoney();

	public function getGameIdEntry();
}