<?php
namespace system;

class filecontrol {
	/**
	 * 读取文件夹内文件
	 * @param  string  $dirpath 要读取的文件夹地址
	 * @param  string  $type    要取出的文件类型
	 * @param  boolean $current 是否读取文件夹内的文件夹
	 * @return  array  $files    返回读取结果
	 */
	public function fileList($dirpath, $type = "", $current = false) {
		$files = array();
		$arr = array();
		$dirpath = rtrim($dirpath, D);
		if (!file_exists($dirpath)) {return $files;}
		$handler = opendir($dirpath);
		while (($filename = readdir($handler)) !== false) {
			if ($filename != "." && $filename != "..") {
				if ($current && is_dir($dirpath . D . $filename)) {
					$arr = $this->fileList($dirpath . D . $filename, $type, $current);
					$files = array_merge($files, $arr);
				}
				if (!empty($type)) {
					$extend = pathinfo($filename);
					if (empty($extend['extension']) || $extend['extension'] != $type) {
						if ($type != "dir" && empty($extend['extension'])) {
							continue;
						}
					}
				}
				$files[] = $dirpath . D . $filename;
			}
		}
		closedir($handler);
		return $files;
	}

	//删除指定文件夹下指定类型的文件或全部文件
	public function delFile($dir, $type = "", $current = false) {
		$dirArr = array();
		if (is_dir($dir)) {
			$fileArr = $this->fileList($dir, $type, $current);
			//删除文件
			foreach ($fileArr as $filename) {
				if (is_dir($filename)) {
					$dirArr[] = $filename;
				} else {
					unlink($filename);
				}
			}
			//删除目录
			foreach ($dirArr as $filename) {
				@rmdir($filename);
			}
		}
		return True;
	}

	/**
	 * 将数组保存成php文件
	 * @param  string $filePath php文件地址
	 * @param  array $arrData  数组数据
	 * @return bool       True/False
	 */
	public function phpWrite($filePath, $arrData) {
		if (empty($arrData) || empty($filePath)) {echo 'phpWrite havnt data';exit;}
		$html = '<?php return ' . var_export($arrData, true) . ';?>';
		return file_put_contents($filePath, $html);
	}
}
?>