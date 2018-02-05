<?php

namespace Honey\item;

class MagicGold extends MagicItem{

	public function __construct(int $meta = 0, $nbt = null){
		parent::__construct(self::GOLD_INGOT, $meta, "Magic Gold");
		if($nbt !== null){
			$this->setCompoundTag($nbt);
		}
	}

	public function transferToItemBox($player, $account){
		//ここにItemBoxへの転送処理を書く
	}
}