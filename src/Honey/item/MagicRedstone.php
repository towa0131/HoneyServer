<?php

namespace Honey\item;

class MagicRedstone extends MagicItem{

	public function __construct(int $meta = 0, $nbt = null){
		parent::__construct(self::REDSTONE, $meta, "Magic Redstone");
		if($nbt !== null){
			$this->setCompoundTag($nbt);
		}
	}

	public function transferToItemBox($player, $account){
		//ここにItemBoxへの転送処理を書く
	}
}