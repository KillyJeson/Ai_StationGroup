<?php
namespace system;

class facade {
	public static $objs = null, $config = null;
	public static $curObj = null, $modName = null;

	//静态调用
	public static function __callstatic($classname, $data = array()) {
		static::loadModObj();
		if (!method_exists(static::$curObj, $classname) && !method_exists(static::$curObj, "__call")) {return;}
		return call_user_func_array(array(static::$curObj, $classname), $data);
	}

	//加载模块配置文件
	public static function loadConfig($fileName = "config", $data = array()) {
		static::loadModObj();
		if (is_null(self::$config)) {self::$config = self::$objs["system"]->config();}
		if (count($data) < 1) {return self::$config[static::$modName["0"] . "/" . $fileName];}
		self::$config[static::$modName["0"] . "/" . $fileName] = $data;
	}

	protected static function loadModObj() {
		if (is_null(static::$curObj)) {
			if (is_null(static::$modName)) {static::$modName = explode("\\", get_called_class());}
			if (!isset(self::$objs["system"])) {self::$objs["system"] = new container();}
			if (!isset(self::$objs[static::$modName["0"]])) {self::$objs[static::$modName["0"]] = self::$objs["system"]->switchModular(static::$modName["0"]);}
			if (isset(static::$modName["1"])) {
				$class = str_replace("Ai_", "", static::$modName["1"]);
				static::$curObj = self::$objs[static::$modName["0"]]->{$class}();
			} else {static::$curObj = self::$objs[static::$modName["0"]];}
		}
	}
}
