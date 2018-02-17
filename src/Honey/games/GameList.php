<?php

namespace Honey\games;

class GameList{

	const GAME_MINECRASH = 0;
	const GAME_SHARP4PROT3 = 1;
	const GAME_SHARP2PROT2 = 2;
	const GAME_FFA = 3;

	const NAME_MINECRASH = "MineCrash";
	const NAME_SHARP4PROT3 = "Sharp4Prot3";
	const NAME_SHARP2PROT2 = "Sharp2Prot2";
	const NAME_FFA = "FFA";

	const ICON_MINECRASH = 265;
	const ICON_SHARP4PROT3 = 276;
	const ICON_SHARP2PROT2 = 311;
	const ICON_FFA = 346;

	const GAMELIST = [self::GAME_MINECRASH,
					self::GAME_SHARP4PROT3,
					self::GAME_SHARP2PROT2,
					self::GAME_FFA
					];

	const NAMELIST = [self::NAME_MINECRASH,
					self::NAME_SHARP4PROT3,
					self::NAME_SHARP2PROT2,
					self::NAME_FFA
					];

	const ICONLIST = [self::ICON_MINECRASH,
					self::ICON_SHARP4PROT3,
					self::ICON_SHARP2PROT2,
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