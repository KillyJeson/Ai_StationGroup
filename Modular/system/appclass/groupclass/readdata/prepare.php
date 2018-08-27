<?php
namespace system;

class prepare extends connect {
	private static $stmt;
	/**
	 * 预处理查询
	 * @param  [string] $table [数据表名]
	 * @param  [strOrArr] $filed [字段]
	 * @param  [strOrArr] $post  [数据]
	 * @param  [strOrArr] $where [条件]
	 * @return [array]        [结果]
	 */
	public function select($table, $filed, $post, $where) {
		$this->filedWherePost($filed, $post, $where);
		$sql = "select " . $filed . " from" . $table . " where " . $where;
		return $this->preMain($sql, $post, True);
	}

	/**
	 * 预处理新增
	 * @param  [string] $table [数据表名]
	 * @param  [strOrArr] $filed [字段]
	 * @param  [strOrArr] $post  [数据]
	 * @return [array]        [结果]
	 */
	public function insert($table, $filed, $post) {
		$this->filedWherePost($filed, $post);
		$sql = "insert into " . $table . " set " . $filed . " ON DUPLICATE KEY UPDATE c=c+1";
		return $this->preMain($sql, $post);
	}

	/**
	 * 预处理更新
	 * @param  [string] $table [数据表名]
	 * @param  [strOrArr] $filed [字段]
	 * @param  [strOrArr] $post  [数据]
	 * @param  [strOrArr] $where [条件]
	 * @return [array]        [结果]
	 */
	public function update($table, $filed, $post, $where) {
		$this->filedWherePost($filed, $post, $where);
		$sql = "update " . $table . " set " . $filed . " where " . $where;
		return $this->preMain($sql, $post);
	}

	/**
	 * 预处理删除
	 * @param  [string] $table [数据表名]
	 * @param  [strOrArr] $filed [字段]
	 * @param  [strOrArr] $post  [数据]
	 * @param  [strOrArr] $where [条件]
	 * @return [array]        [结果]
	 */
	public function delete($table, $filed, $post, $where) {
		$this->filedWherePost($filed, $post, $where);
		$sql = "delete from " . $table . " where " . $where;
		return $this->preMain($sql, $post);
	}

	//结构化需要处理的语句
	private function filedWherePost(&$filed, &$post, &$where = "") {
		$arr = static::whereArr($filed, $where);
		$post = static::postArr($filed, $post);
		$filed = implode(",", $arr["0"]);
		$where = implode(" and ", $arr["1"]);
	}

	//执行预处理数据
	private function preMain($sql, $post_arr) {
		$sub = base64_encode($sql);
		if (!isset(self::$stmt[$sub])) {
			self::$stmt[$sub] = $this->prepare($sql);
		}
		foreach ($post_arr as $k => $post2) {
			self::$stmt[$sub]->execute($post2);
		}
		$type = strtolower(substr($sql, 0, 6));
		if ($type == "select") {
			$rows = self::$stmt[$sub]->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$rows = self::$stmt[$sub]->rowCount();
		}
		return $rows;
	}

	//根据条件元素获得预处理中的条件语句
	private static function whereArr($filed, $where) {
		$fileds = array();
		$wheres = array();
		$where_arr = array();
		is_array($filed) ? $fileds = $filed : $fileds[] = $filed;
		is_array($where) ? $wheres = $where : $wheres[] = $where;
		$filed_arr = static::fileArr($filed);
		$wheres = array_reverse($wheres);
		foreach ($wheres as $k => $value) {
			$ind = array_keys($filed, $value);
			$nub = end($ind);
			if (is_numeric($nub)) {
				$where_arr[] = $filed_arr[$nub];
				unset($filed_arr[$nub]);
				unset($filed[$nub]);
			}
		}
		$where_arr = array_reverse($where_arr);
		return array($filed_arr, $where_arr);
	}

	//根据条件元素制作对应的元素预处理部份
	private static function fileArr($filed) {
		$fileds = array();
		$filed_arr = array();
		$arr = array();
		is_array($filed) ? $fileds = $filed : $fileds[] = $filed;
		foreach ($fileds as $k => $filed) {
			$nub = static::arrSubNumber($arr, $filed);
			$arr[$nub] = array();
			$filed_arr[$k] = $filed . "=:" . $nub;
		}
		return $filed_arr;
	}

	//根据条件元素及传入的值制作预处理中的数据传入部分
	private static function postArr($filed, $post) {
		$fileds = array();
		$posts = array();
		$post_arr = array();
		is_array($filed) ? $fileds = $filed : $fileds[] = $filed;
		is_array($post) ? $posts = $post : $posts[] = $post;
		foreach ($posts as $k => $post) {
			$posts2 = array();
			is_array($post) ? $posts2 = $post : $posts2[] = $post;
			foreach ($posts2 as $s => $post2) {
				if (!isset($fileds[$s])) {continue;}
				if (!isset($post_arr[$k])) {$post_arr[$k] = array();}
				$nub = static::arrSubNumber($post_arr[$k], ":" . $fileds[$s]);
				$post_arr[$k][$nub] = $post2;
			}
		}
		return $post_arr;
	}

	//处理数组中重复的下标
	private static function arrSubNumber($arr, $str, $num = 0) {
		$result = "";
		$num++;
		if (!isset($arr[$str])) {return $str;}
		if (isset($arr[$str . $num])) {
			return static::arrSubNumber($arr, $str, $num);
		} else {
			return $str . $num;
		}
	}
}