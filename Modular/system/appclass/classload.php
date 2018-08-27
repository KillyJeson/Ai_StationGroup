<?php
namespace system;

class classload {
	public static $configCache;
	public static $update = false;
	//自动加载类文件和facade类运行
	public static function autoload($classname) {
		$arr = explode("\\", trim($classname, "\\"));
		if (isset($arr["1"]) && !strstr($arr["1"], "Ai_")) {
			$arr = array_map("strtolower", $arr);
			$path = MP . $arr["0"] . D . "appclass" . D . $arr["1"] . ".php";
			if (!file_exists($path)) {$path = static::checkConfig($arr);}
			if (file_exists($path)) {include $path;}
			return;
		}
		if (isset($arr["1"])) {
			$doPhp = "namespace " . $arr["0"] . ";class " . $arr["1"] . " extends \\system\\facade {public static \$curObj = Null, \$modName = Null;}";
		} else {
			$doPhp = "class " . $arr["0"] . " extends \\system\\facade {public static \$curObj = Null, \$modName = Null;}";
		}
		eval($doPhp);
	}

	//通过文件缓存 或 全局扫描获取类文件
	public static function loadConfig($update = false) {
		$path = SP . "filecache" . D . "registy.php";
		if (!file_exists($path) || $update) {
			static::$configCache = Register::scannedClassFile();
			static::$update = True;
		} else {
			static::$configCache = include $path;
		}
	}

	//检测类文件是否注册和类文件是否还存在
	public static function checkConfig($arr) {
		$configPath = "";
		if (isset(static::$configCache[$arr["0"]][$arr["1"]]["path"])) {
			$configPath = static::$configCache[$arr["0"]][$arr["1"]]["path"];
		}
		$path = MP . str_replace(array("\\", "/"), D, $configPath) . end($arr) . ".php";
		if ((!isset($configPath) || !file_exists($path))) {
			if (static::$update == false) {static::loadConfig(True);} else {return Null;}
			if (!isset(static::$configCache[$arr["0"]][$arr["1"]]["path"])) {return Null;}
			$path = MP . str_replace(array("\\", "/"), D, static::$configCache[$arr["0"]][$arr["1"]]["path"]) . end($arr) . ".php";
		}
		return $path;
	}
}