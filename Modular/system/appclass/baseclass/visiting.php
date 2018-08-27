<?php
namespace system;

use system\Ai_website as website;

class visiting {
	private static $_currentUrl = null;
	private static $_visitingInfo = null;
	public function __construct() {
		if (empty($_SERVER["HTTP_HOST"]) || empty($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER["REMOTE_ADDR"])) {
			echo "The visiting module must be executed in the browser state";exit;
		}
	}

	public function visitingInfo() {
		if (is_null(self::$_visitingInfo)) {
			self::$_visitingInfo = $this->getVisitingInfo();
		}
		return self::$_visitingInfo;
	}

	public function currentUrl() {
		if (is_null(self::$_currentUrl)) {
			self::$_currentUrl = $this->getCurrentUrl();
		}
		return self::$_currentUrl;
	}

	public function getRequest() {
		$result = $this->currentUrl();
		if (isset($_REQUEST)) {
			$result["query"] = $this->arrsToArr($_REQUEST);
		}
		return $result;
	}

	//如果传递参数为数组形式只取最低层数据
	private function arrsToArr($data) {
		$result = array();
		if (!is_array($data)) {return $data;}
		foreach ($data as $k => $val) {
			$result[$k] = $this->arrsToArr($val);
		}
		$result = array_filter($result);
		return $result;
	}

	// 获取蜘蛛访问数据
	private function getVisitingInfo() {
		$useragent = '';
		$cip = '';
		$useragent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
		if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		} elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (!empty($_SERVER["REMOTE_ADDR"])) {
			$cip = $_SERVER["REMOTE_ADDR"];
		}
		return array($useragent, $cip);
	}

	// 获取当前访问网址
	private function getCurrentUrl() {
		$weburl = $_SERVER['HTTP_HOST'] ?: $weburl = $_SERVER['SERVER_NAME'];
		$protocol = explode("/", $_SERVER["SERVER_PROTOCOL"]);
		$weburl = strtolower($weburl);
		$protocol = strtolower($protocol["0"]);
		return website::urlResolution($protocol . "://" . $weburl . $_SERVER['REQUEST_URI']);
	}
}
