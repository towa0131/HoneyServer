<?php

namespace Honey\item;

class MagicLapisLazuli extends MagicItem{

	public function __construct(int $meta = 4, $nbt = null){
		parent::__construct(self::DYE, $meta, "Magic LapisLazuli");
		if($nbt !== null){
			$this->setCompoundTag($nbt);
		}
	}

	public function transferToItemBox($player, $account){
		//ここにItemBoxへの転送処理を書く
	}
}