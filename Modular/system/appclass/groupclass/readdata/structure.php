<?php
namespace system;

class structure extends connect {
	private static $page_data; //分页SQL
	//执行select语句
	public function select(array $data) {
		if (empty($data['filed'])) {$data['filed'] = '*';} else { $data['filed'] = $this->standard($data['filed']);}
		if (!empty($data['order'])) {
			if (strpos($data['order'], '.')) {$data['order'] = self::$prefix[$this->dbname] . $data['order'];}
			$data['order'] = ' order by ' . $data['order'];
		} else {
			$data['order'] = '';
		}
		if (!empty($data['table'])) {$data['table'] = $this->standard($data['table'], 'from');}
		if (!empty($data['where'])) {$data['where'] = ' where ' . $this->standard($data['where'], 'where');} else { $data['where'] = '';}
		if (!empty($data['limit'])) {$data['limit'] = ' limit ' . $data['limit'];} else { $data['limit'] = '';}
		self::$page_data[$this->dbname] = $data;
		self::$lastsql[$this->dbname] = "select " . trim($data['filed']) . " from " . $data['table'] . $data['where'] . $data['order'] . $data['limit'];
		return $this->dosql();
	}

	//执行insert语句
	public function insert(array $data) {
		if (!$data) {return;}
		$data_var = "";
		$table = $data["table"];
		unset($data["table"]);
		$table = self::$prefix[$this->dbname] . $table;
		$arr_k = $this->standard(implode(',', array_keys($data)));
		$arr_var = array_values($data);
		foreach ($arr_var as $k => $var) {
			$k == (count($arr_var) - 1) ? $data_var .= "'" . $var . "'" : $data_var .= "'" . $var . "',";
		}
		$sql = "insert into " . $table . "(" . $arr_k . ") values(" . $data_var . ")";
		self::$lastsql[$this->dbname] = $sql;
		return $this->dosql();
	}

	//更新数据表
	public function update(array $date) {
		//安全考虑,阻止全表更新
		if (!empty($date['table'])) {$table = $this->standard($date['table'], 'from');unset($date['table']);}
		if (!empty($date['where'])) {$where = ' where ' . $this->standard($date['where'], 'where');unset($date['where']);} else { $where = '';}
		if (empty($where)) {return false;}
		foreach ($date as $k => $v) {
			$valArr[] = '`' . $k . '` = "' . $v . '"';
		}
		$valStr = implode(',', $valArr);
		$sql = "update " . $table . " set " . trim($valStr) . " " . $where;
		self::$lastsql[$this->dbname] = $sql;
		return $this->dosql();
	}

	public function delete(array $date) {
		//安全考虑,阻止全表更新
		if (!empty($date['table'])) {$table = $this->standard($date['table'], 'from');unset($date['table']);}
		if (!empty($date['where'])) {$where = ' where ' . $this->standard($date['where'], 'where');unset($date['where']);} else { $where = '';}
		if (empty($where)) {return false;}
		$sql = "delete from " . $table . " " . $where;
		self::$lastsql[$this->dbname] = $sql;
		return $this->dosql();
	}

	//获取分页信息
	public function page() {
		if (!isset(self::$page_date[$this->dbname])) {return;}
		$date = self::$page_date[$this->dbname];
		$limit = $date['limit'];
		if (!empty($limit) && strpos($limit, ',')) {
			$arr = explode(',', $limit);
			self::$lastsql[$this->dbname] = "select count(*) as zsl from " . $date['table'] . $date['where'];
			$count = $this->dosql();
			$zcount = $count[0]['zsl'];
			$zpage = floor($zcount / $arr[1]) + 1;
		}
		return array($zcount, $zpage);
	}

	//sql语句标准化
	private function standard($var, $type = false) {
		$arr2 = $var;
		if ($type == 'where' && is_string($var)) {
			$var = explode(' and ', $var);
			$vars2 = array();
			foreach ($var as $k => $vars) {
				$vars = str_replace("'", '"', $vars);
				preg_match('/(.*?)(!=|>=|<=|<>|>|<|=|like)(.*)/is', $vars, $varss);
				$varss[1] = str_replace('`', '', trim($varss[1]));
				$varss[3] = str_replace('"', '', trim($varss[3]));
				if (strpos($varss[1], '.')) {
					$varss[1] = self::$prefix[$this->dbname] . trim($varss[1]);
				} else {
					$varss[1] = '`' . $varss[1] . '` ';
				}
				if (strpos($varss[3], '.') && strpos($varss[1], '.')) {
					$varss[3] = self::$prefix[$this->dbname] . trim($varss[3]);
				} else {
					$varss[3] = '"' . $varss[3] . '"';
				}
				$vars2[] = $varss[1] . $varss[2] . $varss[3];
			}
			$arr2 = implode(' and ', $vars2);
		}
		if ($type == 'from') {
			$table = "";
			if (strpos($var, ",")) {
				$var = explode(',', $var);
				foreach ($var as $vars) {$table[] = self::$prefix[$this->dbname] . $vars;}
				$var = implode(',', $table);
			} else {
				$var = self::$prefix[$this->dbname] . $var;
			}
		}
		if (is_string($var) && !strpos($var, '`') && '*' !== $var) {
			$arr2 = '`' . $var . '`';
			if (strpos($var, ',')) {
				$arr2 = array();
				$arr = explode(',', $var);
				foreach ($arr as $k => $arrs) {
					$arr2[$k] = '`' . $arrs . '`';
				}
				$arr2 = implode(', ', $arr2);
			}
		}
		return $arr2;
	}
}