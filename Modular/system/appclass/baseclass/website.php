<?php
namespace system;

class website {
	//网址分解成参数
	public function urlResolution($url) {
		$arr = parse_url($url);
		if (!empty($arr["host"])) {
			$arr2 = $this->subDomain($arr["host"]);
			if ($arr2["2"]) {$arr2["2"] .= ".";}
			$arr["subdomain"] = $arr2["2"];
			$arr["domain"] = $arr2["1"];
			unset($arr["host"]);
		}
		if (!empty($arr["scheme"])) {$arr["scheme"] = strtolower($arr["scheme"]);}
		if (!empty($arr["query"])) {$arr["query"] = "?" . $arr["query"];} else { $arr["query"] = "";}
		if (empty($arr["path"])) {
			$arr["path"] = "/";
		}
		$url2 = pathinfo($arr["path"]);
		$dirname = trim($url2["dirname"], ".\\/") . "/";
		if (empty($url2["extension"])) {
			if ($url2["basename"]) {$dirname .= $url2["basename"] . "/";}
			$filename = "";
			$extension = "";
		} else {
			$filename = $url2["filename"];
			$extension = $url2["extension"];
		}
		unset($arr["path"]);
		$dirname = strtolower(trim($dirname, "/"));
		$arr["dirname"] = explode("/", $dirname);
		$arr["filename"] = strtolower($filename);
		$arr["extension"] = strtolower($extension);
		return $arr;
	}

	//默认页名称及文件类型
	public function def($data) {
		$dirname = implode(D, $data["dirname"]);
		empty($dirname) ?: $dirname .= D;
		!empty($data["filename"]) ?: $data["filename"] = "index";
		!empty($data["extension"]) ?: $data["extension"] = "php";
		$filePath = $dirname . $data["filename"] . "." . $data["extension"];
		return $filePath;
	}

	//域名分解
	private static function subDomain($domain) {
		$a = 0;
		$b = '';
		$c = '';
		$arr = explode('.', $domain);
		if (count($arr) <= 2) {
			$b = $domain;
		} elseif (count($arr) == 3 && $arr["0"] == "www") {
			$b = $arr["1"] . "." . $arr["2"];
			$c = $arr["0"];
		} else {
			$g = explode('|', 'af|aq|at|au|be|bg|br|ca|ch|cl|cn|de|eg|es|fi|fr|gr|hk|hu|ie|il|in|iq|ir|is|it|jp|kr|mx|nl|no|nz|pe|ph|pr|pt|ru|se|sg|th|tr|tw|uk|us|za|cc'); //允许的国家域名
			$j = array('com', 'net', 'edu', 'org', 'gov');
			$n = 0;
			$last = array_slice($arr, -2);
			if (array_intersect($g, $last)) {$n++;}
			if (array_intersect($j, $last)) {$n++;}
			$b = join(".", array_slice($arr, -($n + 1)));
			$c = join(".", array_slice($arr, 0, (count($arr) - ($n + 1))));
			if ($c != "www") {$a = 1;}
		}
		return array($a, $b, $c);
	}
}
