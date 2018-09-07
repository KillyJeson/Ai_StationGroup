<?php
namespace cms;

class router {
	private static $con;
	public function __construct($data = array()) {
		$type = "show";
		$pageType = "index";
		//判断是不是后台地址
		if (!empty($data["dirname"])) {
			$arr = explode("/", $data["dirname"]);
			$first = strtolower(array_shift($arr));
			if ($first == "admin") {
				$type = "admin";
				$data["dirname"] = implode("/", $arr);
			}
		}

		//判断进入哪种页面
		if (!empty($data["dirname"])) {
			$pageType = "list";
		} elseif (!empty($data["filename"])) {
			$pageType = "show";
		}

		//创建数据库连接
		if (!isset(self::$con)) {
			self::$con = \cms::readdata();
		}
		$this->show($type, $pageType, $data);
	}

	private function show($type, $pageType, $data) {
		include MP . "cms" . D . $type . D . $pageType . ".php";
	}
}

?>