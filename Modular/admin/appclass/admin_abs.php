<?php
namespace admin;

abstract class admin_abs {
	public static $data;
	public static $currMod;
	public function toDo($currMod, $data) {
		static::$currMod = $currMod;
		static::$data = $data;
		$class = reset(static::$data["dirname"]);
		if (!method_exists($this, $class)) {$class = "select";}
		return $this->{$class}();
	}

	public function select() {
		$config = static::$currMod::loadConfig();
		return $config;
	}
}