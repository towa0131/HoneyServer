<?php

namespace Honey\account;

use pocketmine\Player;

use Honey\Main;

use Honey\utils\DB;

class AccountManager{

	/** @var Account[] */
	private static $accounts = [];
	/** @var ??? */
	private static $inventory_cache = [];

	/**
	   * プレイヤーオブジェクトからアカウントを取得します
	   *
	   * @param Player $player
	   *
	   * @return Account|null | アカウントデータ
	   */
	public static function getAccount(Player $player){
		$name = $player->getName();
		$xuid = $player->getXUID();
		if(self::hasAccount($xuid)){
			if(!isset(self::$accounts[$xuid])){
				$account = new Account($xuid);
				self::$accounts[$xuid] = $account;
			}
			return self::$accounts[$xuid];
		}
		return null;
	}

	/**
	   * 名前からアカウントを取得します(オフラインユーザーの取得の時とかに使います)
	   *
	   * @param string $name
	   *
	   * @return Account|null | アカウントデータ
	   */
	public static function getAccountByName($name){
		$xuid = self::getXuidByName($name);
		if(!is_null($xuid)){
			if(self::hasAccount($xuid)){
				if(!isset(self::$accounts[$xuid])){
					$account = new Account($xuid);
					self::$accounts[$xuid] = $account;
				}
				return self::$accounts[$xuid];
			}
		}
		return null;
	}

	/**
	   * Xuidからアカウントを取得できます
	   *
	   * @param string $xuid
	   *
	   * @return Account|null | アカウントデータ
	   */
	public static function getAccountByXuid($xuid){
		if(self::hasAccount($xuid)){
			if(!isset(self::$accounts[$xuid])){
				$account = new Account($xuid);
				self::$accounts[$xuid] = $account;
			}
			return self::$accounts[$xuid];
		}
		return null;
	}

	/**
	   * @param string $xuid
	   *
	   * @return bool
	   */
	public static function hasAccount($xuid){
		$db = DB::getDB();
		$data = "SELECT * FROM xuids";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($xuid == $row["xuid"]){
					$name = $row["name"];
					break;
				}
			}
			if(!isset($name)){
				return false;
			}
		}
		$data = "SELECT * FROM logindata";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($xuid == $row["xuid"]){
					return true;
				}
			}
		}
		return false;
	}

	/**
	   * @param string $name
	   *
	   * @return string
	   */
	public static function getXuidByName($name){
		$db = DB::getDB();
		$data = "SELECT * FROM xuids";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($name == $row["name"]){
					$xuid = $row["xuid"];
					break;
				}
			}
			if(!isset($xuid)){
				return null;
			}
		}
		$data = "SELECT * FROM logindata";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($xuid == $row["xuid"]){
					return $xuid;
				}
			}
		}
		return null;
	}
}