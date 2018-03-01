<?php

namespace Honey\utils;

use pocketmine\Server;

use Honey\Main;

class Proxy{

	private $ip;
	private $proxy;
	private $asn;
	private $host;
	private $countryCode;
	private $countryName;

	public function __construct(string $ip){
		$this->ip = $ip;
		$value = $this->getIpData();
		$this->proxy = $value["proxy"];
		$this->asn = $value["asn"];
		$this->host = $value["hostname"];
		$this->countryCode = $value["countryCode"];
		$this->countryName = $value["countryName"];
	}

	protected function getIpData(){
		$result = json_decode(file_get_contents("http://legacy.iphub.info/api.php?ip=" . $this->getIp() .  "&showtype=4"), true);
		return $result;
	}

	public function getIp(){
		return $this->ip;
	}

	public function setIp(string $ip){
		$this->ip = $ip;
	}

	public function isProxy(){
		return $this->proxy;
	}

	public function setProxy(bool $proxy){
		$this->proxy = $proxy;
	}

	public function getAsn(){
		return $this->asn;
	}

	public function setAsn(string $asn){
		$this->asn = $asn;
	}

	public function getHost(){
		return $this->host;
	}

	public function setHost(string $host){
		$this->host = $host;
	}

	public function getCountryCode(){
		return $this->countryCode;
	}

	public function setCountryCode(string $countryCode){
		$this->countryCode = $countryCode;
	}

	public function getCountryName(){
		return $this->countryName;
	}

	public function setCountryName(string $countryName){
		$this->countryName = $countryName;
	}	
}