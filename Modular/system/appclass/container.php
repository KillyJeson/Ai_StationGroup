<?php
namespace system;

class container {
	private static $appObjs; //存储类
	public $mod = "system";
	//初始化system模块控制器
	public function __construct() {
		if (!isset(self::$appObjs["system"])) {self::$appObjs["system"] = $this;}
	}

	//创建单个模块控制器
	public function switchModular($mod = "system") {
		if (isset(self::$appObjs[$mod])) {return self::$appObjs[$mod];}
		self::$appObjs[$mod] = clone self::$appObjs["system"];
		foreach (self::$appObjs[$mod] as $k => $var) {unset(self::$appObjs[$mod]->$k);}
		self::$appObjs[$mod]->mod = $mod;
		return self::$appObjs[$mod];
	}

	public function objOut() {
		return self::$appObjs;
	}

	//生成模块内的对像
	private function appClass($obj, $data) {
		if (isset($data[$obj])) {$objs = $data[$obj];unset($data[$obj]);} else { $objs = $obj;}
		if (!isset(self::$appObjs[$this->mod]->{$obj})) {
			$newObj = $this->mod . "\\" . $objs;
			$path = classload::checkConfig(array($this->mod, $objs));
			if (is_null($path)) {return;}
			$class = new \ReflectionClass($newObj);
			if ($class->hasMethod("__construct")) {
				self::$appObjs[$this->mod]->{$obj} = $class->newInstanceArgs($data);
			} else {
				self::$appObjs[$this->mod]->{$obj} = $class->newInstance();
			}
		}
		return self::$appObjs[$this->mod]->{$obj};
	}

	//当访问的方法不存在时
	public function __call($classname, $data = array()) {
		if (isset(self::$appObjs[$this->mod]->{$classname})) {return self::$appObjs[$this->mod]->{$classname};}
		if ($classname == "container") {return $this->switchModular($this->mod);}
		return $this->appClass($classname, $data);
	}
}