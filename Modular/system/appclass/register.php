<?php
namespace system;

class register {
	public static $configCache = Null;
	//扫描所有模块、类信息保存成注册表
	public static function register($mod = "") {
		$results = array();
		if (is_null(static::$configCache)) {static::scannedClassFile($mod);}
		if ($mod == "") {Ai_filecontrol::delFile(SP . "filecache" . D . "Comment", "", True);}
		foreach (static::$configCache as $k => $result2) {
			$commentsArr = array();
			foreach ($result2 as $s => $result3) {
				$html = file_get_contents(MP . $result3["path"] . $s . ".php");
				if (!preg_match("/class\s(.*?)\{/is", $html)) {continue;}
				$loadArr = static::reflection($k, $s, $result3["path"]);
				$results[$k][$s] = $loadArr["0"];
				$commentsArr[$k][$s] = $loadArr["1"];
			}
			if ($commentsArr[$k]["router"]["comment"]) {
				$commentsArr[$k]["comment"] = $commentsArr[$k]["router"]["comment"];
			} else {
				$commentsArr[$k]["comment"] = "No Comment";
			}
			Ai_filecontrol::phpWrite(SP . "filecache" . D . "Comment" . D . $k . ".php", $commentsArr);
		}
		foreach ($results as $k => $result) {classload::$configCache[$k] = $result;}
		Ai_filecontrol::phpWrite(SP . "filecache" . D . "registy.php", classload::$configCache);
	}

	//获取所有模块内APPCLASS目录下的类文件
	public static function scannedClassFile($mod = "") {
		$result = array();
		if ($mod == "") {
			$modularFile = Ai_filecontrol::fileList(MP, "dir", false);
		} else { $modularFile = array(MP . $mod);}
		foreach ($modularFile as $k => $mFile) {
			$modularName = str_ireplace(MP, "", $mFile);
			$classFile = Ai_filecontrol::fileList($mFile . D . "appclass", "php", True);
			foreach ($classFile as $s => $fileName) {
				$pathArr = pathinfo($fileName);
				$classPath = str_ireplace(array(MP, ".php"), "", $pathArr["dirname"]);
				$className = $pathArr["filename"];
				$result[$modularName][$className]["path"] = $classPath . D;
			}
		}
		static::$configCache = $result;
		return $result;
	}

	//反射类成员
	protected static function reflection($modularName, $className, $classPath) {
		$result = array();
		$commentArr = array();
		$result = array("path" => $classPath);
		$class = new \ReflectionClass($modularName . "\\" . ucfirst($className));
		$classComment = $class->getDocComment();
		$classComment ? $commentArr["comment"] = $classComment : $commentArr["comment"] = "No Comment";
		$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $k => $method) {
			$name = $method->name;
			$currentClass = $method->class;
			if ($currentClass != $modularName . "\\" . $className) {continue;}
			$comment = $method->getDocComment();
			if (!$comment) {
				$comment = "No Comment";
			}
			$result[$name] = "";
			$commentArr[$name] = $comment;
		}
		return array($result, $commentArr);
	}
}
?>