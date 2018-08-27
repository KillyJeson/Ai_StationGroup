<?php
namespace system;

class character {
	public function checkMobile($mobile) {
		if (preg_match('/1[34578]\d{9}$/', $mobile)) {
			return true;
		}
		return false;
	}

	public function checkEmail($email) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return true;
		}
		return false;
	}

	public function checkToArr($strOrArr, $levels = 1) {
		$levels--;
		is_array($strOrArr) ? $result = $strOrArr : $result[] = $strOrArr;
		for ($i = 0; $i < $levels; $i++) {
			foreach ($result as $k => $arr) {
				$result[$k] = $this->checkToArr($arr, $levels);
			}
		}
		return $result;
	}

	public function createStr($randLength = 6, $addtime = 1, $includenumber = 0) {
		if ($includenumber) {
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
		} else {
			$chars = 'abcdefghijklmnopqrstuvwxyz';
		}
		$len = strlen($chars);
		$randStr = '';
		for ($i = 0; $i < $randLength; $i++) {
			$randStr .= $chars[rand(0, $len - 1)];
		}
		$tokenvalue = $randStr;
		if ($addtime) {
			$tokenvalue = $randStr . time();
		}
		return $tokenvalue;
	}
}
?>