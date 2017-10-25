<?php

namespace Honey\account;

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
	   * @return string | プレイヤーの最終ログイン日時
	   */
	public function getLastLogin(){
		return $this->data["lastlogin"];
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
		$data = "SELECT * FROM xuids";
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
		$data = "SELECT * FROM logindata";
		if($result = $db->query($data)){
			while($row = $result->fetch_assoc()){
				if($this->xuid == $row["xuid"]){
					$this->data["xuid"] = $row["xuid"];
					$this->data["address"] = $row["ip"];
					break;
				}
			}
		}
		$data = "SELECT * FROM playerdata";
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
		if(count($this->data) == 7){
			return true;
		}
		return false;
	}
}