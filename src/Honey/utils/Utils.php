<?php

namespace Honey\utils;

use pocketmine\Server;

use Honey\Main;

class Utils{

	public static function callError($errno){
		$file = __DIR__ . "/../documents/ErrNo.txt";
		$txt = file_get_contents($file);
		$errors = explode("\n", $txt);
		foreach($errors as $e){
			$data = explode(":", $e);
			if($data[0] === $errno){
				echo "Error". $data[0] . ":" . $data[1];
				return true;
			}
		}
		return false;
	}
}