<?php

namespace Honey\utils;

use pocketmine\Server;

use pocketmine\utils\MainLogger;
use pocketmine\utils\Config;

use Honey\Main;

class DB{

	/**
	   * $cache[0]にはmysqliインスタンス(connectDBが呼び出され後)
	   * $cache[1]にはアドレス
	   * $cache[2]にはユーザー名
	   * $cache[3]にはパスワード
	   * $cache[4]にはデータベース名
	   * $cache[5]にはタイムアウト時間がはいっている
	   *
	   * @var mixed[]
	   */
	private static $cache = [null];

	/**
	   * @return mysqli | mysqliのインスタンス
	   */
	public static function getDB(){
		if(self::$cache[0] == null){ //キャッシュにmysqliインスタンスがなければデータベースに接続
			//キャッシュ配列にmysqliインスタンスをいれることで再度使用する時のインスタンス作成を省き、高速化している
			self::$cache[0] = self::connectDB(); 
		}
		return self::$cache[0];
	}

	/**
	   * @return mysqli|null
	   */
	public static function connectDB($isRefresh = false){
		$mysqli = new \mysqli(self::$cache[1], self::$cache[2], self::$cache[3], self::$cache[4]);
		$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::$cache[5]);
		$mysqli->set_charset("utf-8");
		if($mysqli->connect_errno){
			MainLogger::getLogger()->error("§a[はにー]§4DBへの接続時にエラーが発生しました。");
			MainLogger::getLogger()->error("§a[はにー]§4エラーメッセージ : " . $mysqli->connect_error);
			Utils::callError(ErrNo::ERRNO_002);
			return null; //接続時にエラー発生
		}
		if(!$isRefresh){
			MainLogger::getLogger()->info("§a[はにー]§bDBへの接続に成功しました。");
		}
		return $mysqli; 
	}

	/**
	   * @return bool
	   */
	public static function isConnect(){
		if(isset(self::$cache[0]) && self::$cache[0] !== null){
			return true;
		}
		return false;
	}

	public static function resetConnect(){
		$mysqli = &self::$cache[0];
		$mysqli->close(); //closeしてメモリを開放
		$mysqli = null; //$cacheに格納されているmysqliインスタンスを初期化
	}

	public static function refreshConnect(){
		self::$cache[0] = self::connectDB(true); 
	}

	public static function setConfig(...$args){
		foreach($args as $a){
			self::$cache[] = $a; //Configに書かれている情報をキャッシュ配列に格納
		}
	}
}