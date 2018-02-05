<?php

namespace Honey\item;

class MagicIron extends MagicItem{

	public function __construct(int $meta = 0, $nbt = null){
		parent::__construct(self::IRON_INGOT, $meta, "Magic Iron");
		if($nbt !== null){
			$this->setCompoundTag($nbt);
		}
	}

	public function transferToItemBox($player, $account){
		//ここにItemBoxへの転送処理を書く
	}
}