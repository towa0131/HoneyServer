<?php

namespace Honey\account;

use pocketmine\Server;

use Honey\utils\DB;

class Account{

	/** @var string */
	private $xuid;
	/** @var mixed[] */
	private $data = [];
	/** @var Form[] */
	private $form_history = [];

	public function __construct($xuid){
		$this->xuid = $xuid;
		$this->initAccount();
	}

	/**
	   * @return string | プレイヤー名
	   */
	public function getName(){
		return $this->data["name"];
	}

	/**
	   * @return string | プレイヤーの使用言語
	   */
	public function getLanguage(){
		return $this->data["language"];
	}

	/**
	   * @param string $lang
	   */
	public function setLanguage(string $lang){
		AccountManager::updateAccount($this, "playerdata", "language", $lang);
	}

	/**
	   * @return string | プレイヤーのXuid
	   */
	public function getXuid(){
		return $this->data["xuid"];
	}

	/**
	   * @return string | プレイヤーのIPアドレス
	   */
	public function getAddress(){
		return $this->data["address"];
	}

	/**
	   * @return string | プレイヤーのスキンデータ(hex2binで複合化が必要)
	   */
	public function getSkin(){
		return $this->data["skin"];
	}

	/**
	   * @return int | ユーザーの所持はにい
	   */
	public function getHoney(){
		return $this->data["honey"];
	}

	/**
	   * @param int $honey
	   */
	public function setHoney(int $honey){
		AccountManager::updateAccount($this, "playerdata", "honey", $this->data["honey"]);
	}

	/**
	   * @param int $honey
	   */
	public function addHoney(int $honey){
		AccountManager::updateAccount($this, "playerdata", "honey", $honey + $this->data["honey"]);
	}

	/**
	   * @return string | プレイヤーの最終ログイン日時
	   */
	public function getLastLogin(){
		return $this->data["lastlogin"];
	}

	/**
	   * @return string | 使用中のマント
	   */
	public function getCape(){
		return $this->data["cape"];
	}

	/**
	   * @return int | チャンクの表示距離
	   */
	public function getViewDistance(){
		return (int)$this->data["chunk"];
	}

	/**
	   * @return bool
	   */
	public function isShowFloating(){
		if($this->data["floatingtext"]){
			return true;
		}
		return false;
	}

	/**
	   * @return bool
	   */
	public function isShowTemperature(){
		if($this->data["temperature"]){
			return true;
		}
		return false;
	}

	/**
	   * @return bool
	   */
	public function isShowCoordinate(){
		if($this->data["coordinate"]){
			return true;
		}
		return false;
	}

	/**
	   * @return bool
	   */
	public function isOnline(){
		$name = $this->data["name"];
		if(Server::getInstance()->getPlayerExact($name) !== null){
			return true;
		}
		return false;
	}

	/**
	   * @return Form | プレイヤーに送られたフォームオブジェクト全て(サーバー参加時から)
	   */
	public function getAllFormHistories(){
		return $this->form_history;
	}

	/**
	   * $back個前のFormオブジェクトが返り値になる
	   * 例 : $this->form_history = [new TestForm1(), new TestForm2()];
	   * $this->getFormHistory(0) => TestForm2
	   * $this->getFormHistory(1) => TestForm1
	   *
	   * @param int $back
	   *
	   * @return Form|null | プレイヤーに送られたフォームオブジェクト
	   */
	public function getFormHistory($back = 0){
		$history = $this->form_history;
		if(isset($history[count($history) - ($back + 1)])){
			return $history[count($history) - ($back + 1)];
		}
		return null;
	}

	/**
	   * @param Form $form
	   */
	public function addFormHistory($form){
		$this->form_history[] = $form;
	}

	/**
	   * @return bool
	   */
	private function initAccount(){
		$db = DB::getDB();
		$data = "SELECT * FROM xuids where xuid = '" . $this->xuid . "'";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($this->xuid == $row["xuid"]){
					$this->data["name"] = $row["name"];
					break;
				}
			}
			if(!isset($this->data["name"])){
				return false;
			}
		}
		$data = "SELECT * FROM logindata where xuid = '" . $this->xuid . "'";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($this->xuid == $row["xuid"]){
					$this->data["xuid"] = $row["xuid"];
					$this->data["address"] = $row["ip"];
					break;
				}
			}
		}
		$data = "SELECT * FROM playerdata where xuid = '" . $this->xuid . "'";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($this->xuid == $row["xuid"]){
					$this->data["honey"] = $row["honey"];
					$this->data["language"] = $row["language"];
					$this->data["skin"] = $row["skin"];
					$this->data["lastlogin"] = $row["lastlogin"];
					break;
				}
			}
		}
		$data = "SELECT * FROM minecrash where xuid = '" . $this->xuid . "'";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($this->xuid == $row["xuid"]){
					$this->data["floatingtext"] = (bool)$row["floatingtext"]; //採掘時の浮遊文字の表示
					$this->data["temperature"] = (bool)$row["temperature"]; //気温とか天気の表示
					$this->data["coordinate"] = (bool)$row["coordinate"]; //座標の表示
					$this->data["cape"] = $row["cape"]; //使用中のマント
					$this->data["chunk"] = $row["chunk"]; //チャンク表示距離
				}
			}
		}
		if(count($this->data) == 12){
			return true;
		}
		return false;
	}
}