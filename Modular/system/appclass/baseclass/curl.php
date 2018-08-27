<?php
namespace system;

class curl {
	private $ips = Null;

	//随机调用IP
	public function ips($ips = array()) {
		if (count($ips) >= 2) {$this->ips = $ips;}
		if (!is_null($this->ips)) {return $this->ips[array_rand($this->ips)];}
		return;
	}

	//多线程抓取网页数据并返回需要的结果
	public function getData($type, $keywords, $thread, $regs, $pixUrl = "") {
		$urls = array();
		$result = array();
		is_array($keywords) ? $keywords_arr = $keywords : $keywords_arr[] = $keywords;
		is_array($type) ? $result_type = $type : $result_type[] = $type;
		foreach ($keywords_arr as $k => $keyword) {
			if ($pixUrl) {$keyword = $pixUrl . urlencode($keyword);}
			$urls[$k] = $keyword;
		}
		$url = array_chunk($urls, $thread, true);
		foreach ($url as $values) {
			$html_arr = $this->proxyCurl($values);
			foreach ($result_type as $value) {
				if (!isset($result[$value])) {$result[$value] = array();}
				$arr = self::pregBody($html_arr, $value, $regs);
				foreach ($arr as $k => $val) {$result[$value][$k] = $val;}
			}
		}
		return $result;
	}

	//模拟抓取
	public function proxyCurl($url, $data = array()) {
		$ip = $this->ips();
		$datas = array(
			"header" => False,
			"useragent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36",
			"followlocation" => True,
			"ssl_verifypeer" => False,
			"ssl_verifyhost" => False,
			"autoreferer" => True,
			"returntransfer" => True,
			"connecttimeout" => 30,
			"timeout" => 6,
		);
		if ($ip) {$datas["interface"] = $ip;}
		foreach ($data as $k => $value) {$datas[$k] = $value;}
		if (is_array($url)) {
			return self::doubleCurl($url, $datas);
		} else {
			return self::singerCurl($url, $datas);
		}
	}

	//正则提取网页中需要的数据
	private static function pregBody($html_arr, $preg, $regs) {
		$result = array();
		if (isset($regs[$preg])) {
			foreach ($html_arr as $k => $values) {
				$arr = array();
				$html = $values["body"];
				foreach ($regs[$preg] as $reg) {
					$element = array();
					if (is_array($reg)) {
						$html2 = $html;
						foreach ($reg as $pregs) {
							preg_match_all($pregs, $html2, $element);
							if (!isset($element["1"]["0"])) {break;}
							$html2 = $element["1"]["0"];
						}
					} else {
						preg_match_all($reg, $html, $element);
					}
					$arr = array_merge($arr, $element["1"]);
				}
				$result[$k] = $arr;
			}
		}
		return $result;
	}

	//单线程抓取
	private static function singerCurl($url, $datas) {
		$result = array();
		$datas["url"] = $url;
		$ch = curl_init();
		foreach ($datas as $option => $value) {
			curl_setopt($ch, constant('CURLOPT_' . strtoupper($option)), $value);
		}
		$html = curl_exec($ch);
		$result["info"] = curl_getinfo($ch);
		curl_close($ch);
		$headerSize = $result["info"]["header_size"];
		if ($datas["header"]) {
			$result["header"] = substr($html, 0, $headerSize);
			$result["body"] = substr($html, $headerSize);
		} else {
			$result["body"] = $html;
		}
		return $result;
	}

	//多线程抓取
	private static function doubleCurl($array, $datas) {
		$result = array();
		$mh = curl_multi_init();
		foreach ($array as $k => $url) {
			$conn[$k] = curl_init();
			$datas["url"] = $url;
			foreach ($datas as $option => $value) {
				curl_setopt($conn[$k], constant('CURLOPT_' . strtoupper($option)), $value);
			}
			curl_multi_add_handle($mh, $conn[$k]);
		}

		$active = null;
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		while ($active && $mrc == CURLM_OK) {
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}

		foreach ($array as $k => $url) {
			curl_error($conn[$k]);
			$html = curl_multi_getcontent($conn[$k]); //获得返回信息
			$result[$k]["info"] = curl_getinfo($conn[$k]); //返回头信息
			$headerSize = $result[$k]["info"]["header_size"];
			if ($datas["header"]) {
				$result[$k]["header"] = substr($html, 0, $headerSize);
				$result[$k]["body"] = substr($html, $headerSize);
			} else {
				$result[$k]["body"] = $html;
			}
			curl_multi_remove_handle($mh, $conn[$k]); //释放资源
			curl_close($conn[$k]); //关闭语柄

		}
		curl_multi_close($mh);
		return $result;
	}
}
?>