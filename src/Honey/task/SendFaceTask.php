<?php

namespace Honey\task;

use pocketmine\Server;

use pocketmine\scheduler\AsyncTask;

use pocketmine\utils\TextFormat;

class SendFaceTask extends AsyncTask{

	//参考 : https://github.com/Muqsit/FaceLogin

	const HEX_SYMBOL = "e29688";

	const TEXTFORMAT_RGB = [
		[0, 0, 0],
		[0, 0, 170],
		[0, 170, 0],
		[0, 170, 170],
		[170, 0, 0],
		[170, 0, 170],
		[255, 170, 0],
		[170, 170, 170],
		[85, 85, 85],
		[85, 85, 255],
		[85, 255, 85],
		[85, 255, 255],
		[255, 85, 85],
		[255, 85, 255],
		[255, 255, 85],
		[255, 255, 255]
	];

	const TEXTFORMAT_LIST = [
		TextFormat::BLACK,
		TextFormat::DARK_BLUE,
		TextFormat::DARK_GREEN,
		TextFormat::DARK_AQUA,
		TextFormat::DARK_RED,
		TextFormat::DARK_PURPLE,
		TextFormat::GOLD,
		TextFormat::GRAY,
		TextFormat::DARK_GRAY,
		TextFormat::BLUE,
		TextFormat::GREEN,
		TextFormat::AQUA,
		TextFormat::RED,
		TextFormat::LIGHT_PURPLE,
		TextFormat::YELLOW,
		TextFormat::WHITE
	];

	/** @var string */
	private $name;
	/** @var string */
	private $skindata;

	/**
	   * @param  string $name
	   * @param string $skindata
	   */
	public function __construct(string $name, string $skindata){
		$this->name = $name;
		$this->skindata = $skindata;
	}

	private function rgbToTextFormat($r, $g, $b){
		$differenceList = [];
		foreach(self::TEXTFORMAT_RGB as $value){
			$difference = pow($r - $value[0],2) + pow($g - $value[1],2) + pow($b - $value[2],2);
			$differenceList[] = $difference;
		}
		$smallest = min($differenceList);
		$key = array_search($smallest, $differenceList);
		return self::TEXTFORMAT_LIST[$key];
	}

	public function onRun(){
		$symbol = hex2bin(self::HEX_SYMBOL);
		$strArray = [];
		$skin = substr($this->skindata, ($pos = (64 * 8 * 4)) - 4, $pos);
		for($y = 0; $y < 8; ++$y){
			for($x = 1; $x < 9; ++$x){
				if(!isset($strArray[$y])){
					$strArray[$y] = "";
				}
				$key = ((64 * $y) + 8 + $x) * 4;
				$r = ord($skin{$key});
				$g = ord($skin{$key + 1});
				$b = ord($skin{$key + 2});
				$format = $this->rgbToTextFormat($r, $g, $b);
				$strArray[$y] .= $format.$symbol;
			}
		}
		$strArray[1] = $strArray[1] . " §f" . $this->name . "さん";
		$strArray[2] = $strArray[2] . " " . "§fようこそ！はにー鯖へ！";
		$this->setResult(implode("\n", $strArray));
	}

	public function onCompletion(Server $server){
		$player = $server->getPlayerExact($this->name);
		if($player !== null){
			//スキンをチャットに送信
			$player->sendMessage($this->getResult());
		}
	}
}