<?php

namespace Honey\utils;

use Honey\Main;

class DB{

	/**
	*	$cache[0]にはmysqliインスタンス(connectDBが呼び出され後)
	*	$cache[1]にはアドレフ
	*	$cache[2]にはユーザー名
	*	$cache[3]にはパスワード
	*	$cache[4]にはデータベース名
	*	$cache[5]にはタイムアウト時間がはいっている
	*/
	private static $cache = [null,
						Main::getInstance()->config->getNested("DB.address"),
						Main::getInstance()->config->getNested("DB.user"),
						Main::getInstance()->config->getNested("DB.password"),
						Main::getInstance()->config->getNested("DB.database"),
						Main::getInstance()->config->getNested("DB.timeout")
						];

	public static function getDB(){
		if(self::$cache[0] == null){ //キャッシュにmysqliインスタンスがなければデータベースに接続
			self::$cache[0] = self::connectDB();
		}
		return self::$cache[0];
	}

	public static function connectDB(){
		$mysqli = new \mysqli(self::$cache[1], self::$cache[2], self::$cache[3], self::$cache[4]);
		$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::$cache[5]);
		if($mysqli->connect_errno){
			return null; //接続時にエラー発生
		}
		return $mysqli;
	}

	public static function resetConnect(){
		self::$cache[0] = null; //$cacheに格納されているmysqliインスタンスを初期化
	}
}