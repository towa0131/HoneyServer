<?php

namespace Honey;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class PlayerModule{

	/**
	   * @param Player $player
	   */
	public function getTemperature($player){
		//TODO
		//気温はバイオームによって変化する
		$biomeId = $player->getLevel()->getChunk($player->x, $player->z);
	}

	/**
	   * @param Player $player
	   * @param Form $form
	   * @param int $formId
	   */
	public function sendForm($player, $form, $formId){
		$pk = new ModalFormRequestPacket();
		$pk->formId = $formId;
		$pk->formData = json_encode($form->getFormData());
		$player->dataPacket($pk);
	}
}