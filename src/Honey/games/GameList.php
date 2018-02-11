<?php

namespace Honey\games;

class GameList{

	const GAME_MINECRASH = 0;
	const GAME_1VS1 = 1;
	const GAME_FFA = 2;

	const NAME_MINECRASH = "MineCrash";
	const NAME_1VS1 = "1vs1";
	const NAME_FFA = "FFA";

	const ICON_MINECRASH = 265;
	const ICON_1VS1 = 276;
	const ICON_FFA = 346;

	const GAMELIST = [self::GAME_MINECRASH,
					self::GAME_1VS1,
					self::GAME_FFA
					];

	const NAMELIST = [self::NAME_MINECRASH,
					self::NAME_1VS1,
					self::NAME_FFA
					];

	const ICONLIST = [self::ICON_MINECRASH,
					self::ICON_1VS1,
					self::ICON_FFA
					];

	public static function getGameList(){
		return self::GAMELIST;
	}

	public static function getGameNameList(){
		return self::NAMELIST;
	}

	public static function getIconList(){
		return self::ICONLIST;
	}
}