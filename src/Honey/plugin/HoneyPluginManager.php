<?php

namespace Honey\plugin;

class HoneyPluginManager{

	/** @var HoneyPluginBase */
	private static $loadplugins = [];

	/**
	   * @param string $name
	   * @param HoneyPluginBase $plugin
	   */
	public static function addPlugin($name, $plugin){
		self::$loadplugins[$name] = $plugin;
	}

	/**
	   * @param string $name
	   *
	   * @return HoneyPluginBase|null
	   */
	public static function getPlugin($name){
		if(isset(self::$loadplugins[$name])){
			return self::$loadplugins[$name];
		}
		return null;
	}

	/**
	   * @return HoneyPluginBase[]
	   */
	public static function getPlugins(){
		return self::$loadplugins;
	}
}