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
		$xuid = $player->getXuid();
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
		$data = "SELECT * FROM xuids where xuid = '" . $xuid . "'";
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
		$data = "SELECT * FROM logindata where xuid = '" . $xuid . "'";
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
	   * @param string $xuid
	   * @param string $name
	   * @param string $ip
	   * @param string $passwd
	   * @param int $honey
	   * @param string $lang
	   * @param string $skin
	   * @param string $lastlogin
	   *
	   * @return bool
	   */
	public static function registerAccount($xuid, $name, $ip, $passwd, $honey, $lang, $skin, $lastlogin){
		if(self::hasAccount($xuid)){
			return false;
		}
		$db = DB::getDB();
		$query = "INSERT INTO xuids(xuid , name) VALUES ('" . $xuid . "', '" . $name . "')";
		 if(!$db->query($query)){
			return false;
		}
		$query = "INSERT INTO logindata(xuid , ip, password) VALUES ('" . $xuid . "', '" . $ip . "', '" . $passwd . "')";
		 if(!$db->query($query)){
			return false;
		}
		$query = "INSERT INTO playerdata(xuid , honey, language, skin, lastlogin) VALUES ('" . $xuid . "', '" . $honey . "', '" . $lang . "', '" . $skin . "', '" . $lastlogin . "')";
		 if(!$db->query($query)){
			return false;
		}
		$query = "INSERT INTO minecrash(xuid) VALUES ('" . $xuid . "')"; //DB側であれこれするのでXUIDしか必要ない
		 if(!$db->query($query)){
			return false;
		}
		return true;
	}

	/**
	   * @param Account $account
	   * @param string $table
	   * @param string $key
	   * @param mixed $value
	   *
	   * @return bool
	   */
	public static function updateAccount($account, $table, $key, $value){
		$db = DB::getDB();
		$xuid = $account->getXuid();
		$query = "UPDATE " . $table . " SET " . $key . " = '" . $value . "' WHERE xuid = '" . $xuid . "'";
		 if(!$db->query($query)){
			return false;
		}
		$account = new Account($xuid);
		self::$accounts[$xuid] = $account; //アカウントの更新を反映
		return true;
	}

	/**
	   * @param string $name
	   *
	   * @return string
	   */
	public static function getXuidByName($name){
		$db = DB::getDB();
		$data = "SELECT * FROM xuids where name = '" . $name . "'";
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
		$data = "SELECT * FROM logindata where xuid = '" . $xuid . "'";
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