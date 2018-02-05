<?php

namespace Honey\item;

class MagicCoal extends MagicItem{

	public function __construct(int $meta = 0, $nbt = null){
		parent::__construct(self::COAL, $meta, "Magic Coal");
		if($nbt !== null){
			$this->setCompoundTag($nbt);
		}
	}

	public function transferToItemBox($player, $account){
		//ここにItemBoxへの転送処理を書く
	}
}