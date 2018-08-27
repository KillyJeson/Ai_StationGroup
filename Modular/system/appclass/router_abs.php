<?php
namespace system;

abstract class router_abs {
	protected static $data;
	protected static $modular;
	protected static $currMod;
	abstract protected static function router();
	//当传递数据中没有模块值时 默认使用system模块从http访问中获取数据
	public function __construct($data = array()) {
		empty(static::$data) ? static::$data = Ai_visiting::getRequest() : static::$data = $data;
		static::$currMod = explode(D, get_called_class())["0"];
		static::$modular = array_shift(static::$data["dirname"]);
		if (static::$modular == static::$currMod) {return static::show();}
		static::router();
	}

	//访问当前模块下show文件夹中的文件
	protected static function show() {
		$filePath = MP . static::$currMod . D . "show" . D . Ai_website::def(static::$data);
		if (!file_exists($filePath)) {echo "你当前访问的页面不存在";exit;}
		include $filePath;
	}
}