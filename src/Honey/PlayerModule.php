<?php

namespace Honey;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\PhotoTransferPacket;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\item\Item;
use pocketmine\item\WrittenBook;

use pocketmine\item\enchantment\Enchantment;

use Honey\account\AccountManager;

class PlayerModule{

	/** @var PlayerModule */
	private static $instance;

	public function __construct(){
		self::$instance = $this;
	}

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
		$account = AccountManager::getAccount($player);
		if($account !== null){
			$account->addFormHistory($form);
		}
	}

	/**
	   * @param Player $player
	   */
	public function sendLobbyItem(Player $player){
		//本配布(鉱石についての説明とかを記載する予定)
		/*$path = __DIR__ . "/images/";
		$imgdata = file_get_contents($path . "logo.png");
		$photo = new PhotoTransferPacket;
		$photo->photoName = $path . "logo.png";
		$photo->photoData = $imgdata;
		$photo->bookId = "0";
		$player->dataPacket($photo);
		$book = Item::get(Item::WRITTEN_BOOK, 0, 1);
		$nbt = new CompoundTag("", [
			new StringTag("title", "§aサーバーの情報"),
			new StringTag("author", "はにい"),
			new IntTag("generation", WrittenBook::GENERATION_ORIGINAL),
			new IntTag("id", 0),
			new ListTag("pages", [])
		]);
		$book->setNamedTag($nbt);
		for($i=0;$i<20;$i++){
			$book->addPage($i);
		}
		$book->setbookId(0);
		$book->setPageImage(0, $path . "logo.png");
		$player->getInventory()->addItem($book);*/
		$item = Item::get(276, 0, 1);
		ItemProvider::getInstance()->setUndroppable($item);
		$player->getInventory()->addItem($item);
		if($player->isOP()){
			$item = Item::get(369, 0, 1);
			$enchant = Enchantment::getEnchantment(9);
			$enchant->setLevel(1);
			$item->addEnchantment($enchant);
			$item->setCustomName("MasterWand");
			ItemProvider::getInstance()->setUndroppable($item);
			$player->getInventory()->addItem($item);
			$item = Item::get(54, 0, 1);
			$item->setCustomName("ToyBox");
			ItemProvider::getInstance()->setUndroppable($item);
			$player->getInventory()->addItem($item);
		}
	}

	public static function getInstance(){
		return self::$instance;
	}
}