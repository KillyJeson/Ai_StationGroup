<?php
namespace user;

class router {
	public function __construct($data) {
		$modData = array_shift($data["dirname"]);
		if (empty($modData)) {$modData = "index";}

	}

	//检测用户是否登录
	public function login() {

	}
}
