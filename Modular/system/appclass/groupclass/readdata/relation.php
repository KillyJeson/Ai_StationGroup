<?php
namespace system;

class relation extends connect {
	//读取数据及其关系数据
	public function select($data) {
		$result = array();
		$data = $this->uncertain("r", $data);
		$result = $this->select($data);
		if (isset($data["tableRel"])) {
			foreach ($result as $k => $res) {
				$arr = array();
				$relData = array();
				$id = $res["id"];
				$relData["table"] = $data["tableRel"];
				$relData["where"] = $data["table"] . "id=" . $id;
				$arr = $this->rRel($relData, $data["table"]);
				$result[$k] = array_merge_recursive($result[$k], $arr);
			}
		}
		return $result;
	}

	//写入数据
	public function insert($data) {
		$result = array();
		$cheArr = array();
		$id = "";
		$che = false;
		$table = $data["table"];
		$tableRel = $data["tableRel"];
		unset($data["tableRel"]);
		$cheArr = $data;
		$che = $this->uncertain("w", $cheArr);
		unset($data["table"]);
		if (!$che && $this->insert($table, $data)) {
			$id = $this->lastinsertid();
			$tableRel[$table . "id"] = $id;
			$table = $tableRel["table"];
			unset($tableRel["table"]);
			$this->insert($table, $tableRel);
		}
		return $id;
	}

	//修改数据
	public function update($data) {
		$result = array();
		if (!isset($data["where"])) {return;}
		$table = $data["table"];
		$tableRel = $data["tableRel"];
		unset($data["tableRel"]);
		$this->update($data);
		$tableRel["where"] = $table . "id=" . $data["id"];
		return $this->update($tableRel);
	}

	//删除数据
	public function delete($data) {
		$result = array();
		$cheArr = array();
		$bool = false;
		if (!isset($data["where"])) {return;}
		$table = $data["table"];
		$tableRel = $data["tableRel"];
		unset($data["tableRel"]);
		$cheArr = $this->rTable($data);
		foreach ($cheArr as $arr) {
			$delArr = array();
			$delArr["table"] = $tableRel;
			$delArr["where"] = $table . "id=" . $id;
			if ($this->delete($delArr)) {
				$data["where"] = "id=" . $id;
				$bool = $this->delete($data);
			}
		}
		return $bool;
	}

	//通过一个关系表获取其它数据值
	private function rRel($relData, $table) {
		$result = array();
		$relArr = array();
		$relArr = $this->select($relData);
		foreach ($relArr as $k => $value) {
			if (strstr($k, "id")) {
				$k = str_ireplace("id", "", $k);
				if ($k == $table && empty($k)) {continue;}
				$arr = array();
				$arr = $this->select(array("table" => $k, "where" => "id=" . $value));
				$result[$k] = array_merge_recursive($result[$k], $arr);
			}
		}
		return $result;
	}

	//特殊字段处理
	public function uncertain($cuurt, $data) {
		if ($cuurt == "r") {return $data;}
		if ($cuurt == "w") {return false;}
	}
}