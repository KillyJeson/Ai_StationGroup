<?php
namespace system;

use system\Ai_filecontrol as fileC;

class config implements \ArrayAccess {
	protected $configs = array();

	public function offsetGet($key) {
		if (empty($this->configs[$key])) {$this->configs[$key] = static::loadConfig($key);}
		return $this->configs[$key];
	}

	public function offsetSet($key, $value) {
		if (empty($this->configs[$key])) {$this->configs[$key] = static::loadConfig($key);}
		$this->configs[$key] = $value;
		$this->configs[$key]["configUpdate"] = True;
	}

	public function offsetExists($key) {
		return isset($this->configs[$key]);
	}

	public function offsetUnset($key) {
		unset($this->configs[$key]);
	}

	private static function loadConfig($key) {
		$result = array();
		$file_path = str_replace(array("/", "\\"), D, $key);
		$file_path = MP . $file_path . '.php';
		if (file_exists($file_path)) {$result = require $file_path;}
		if (!is_array($result)) {$result = array("configUpdate" => True);}
		return $result;
	}

	public function __destruct() {
		foreach ($this->configs as $k => $value) {
			if (isset($value["configUpdate"])) {
				$file_path = str_replace("/", D, $k);
				$file_path = MP . $file_path . '.php';
				unset($value["configUpdate"]);
				if (count($value) >= 1) {fileC::phpWrite($file_path, $value);}
			}
		}
		if (classload::$update) {
			classload::$update = false;
			Register::register();
		}
	}
}