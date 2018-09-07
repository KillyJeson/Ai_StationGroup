<?php
namespace admin;

use system\classload as classload;
use system\router_abs;

class router extends router_abs {
	protected static function router() {
		if (!isset(classload::$configCache[static::$modular])) {return static::show();}
		$obj = static::$modular::{static::$currMod}();
		if (is_null($obj)) {return;}
		$data_arr = $obj->toDo(static::$modular, static::$data);
		if (is_array($data_arr)) {echo json_encode($data_arr);}
	}
}