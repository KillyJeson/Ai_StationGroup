<?php
namespace system;

class readdata {
	private static $conf;
	public $db_con;
	public $data;

	public function __construct($dbPath) {
		if (!file_exists($dbPath)) {return;}
		$this->db_con = include $dbPath;
	}

	public function select() {
		$this->data = func_get_args();
		return call_user_func_array(array($this->getObj(), "select"), $this->data);
	}

	public function insert() {
		$this->data = func_get_args();
		return call_user_func_array(array($this->getObj(), "insert"), $this->data);
	}

	public function update() {
		$this->data = func_get_args();
		return call_user_func_array(array($this->getObj(), "update"), $this->data);
	}

	public function delete() {
		$this->data = func_get_args();
		return call_user_func_array(array($this->getObj(), "delete"), $this->data);
	}

	public function getObj() {
		if (isset($this->data["0"]["table"])) {
			$con = "structure";
		} elseif (is_string($this->data["0"]["0"])) {
			$con = "prepare";
		} elseif (isset($data["0"]["tableRel"])) {
			$con = "relation";
		} else {
			$con = "structure";
		}
		if (!isset(self::$conf[$con])) {self::$conf[$con] = \system::{$con}($this->db_con);}
		return self::$conf[$con];
	}

	public function __call($classname, $data = array()) {
		return call_user_func_array(array($this->getObj(), $classname), $data);
	}
}
?>