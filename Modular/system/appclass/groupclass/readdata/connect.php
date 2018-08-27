<?php
namespace system;

use PDO;

class connect {
	private static $_act = null; //主数据库连接对像
	public static $lastsql; //最后一条SQL语句
	public static $prefix; //分页SQL
	public $dbname;
	public function __construct($db_con) {
		if (empty($db_con)) {return;}
		$this->creatConnect($db_con);
	}

	//执行SQL
	public function query($sql) {
		self::$lastsql[$this->dbname] = $sql;
		return $this->dosql();
	}

	//预处理
	public function prepare($sql) {
		return self::$_act[$this->dbname]->prepare($sql);
	}

	//返回最后一条执行的sql语句
	public function lastsql() {
		return self::$lastsql[$this->dbname];
	}

	//最后一条插入ID
	public function lastinsertid() {
		return self::$_act[$this->dbname]->lastInsertId();
	}

	//列出数据库表及表信息
	public function showTables($table = "") {
		$result = array();
		if ($table == "") {
			self::$lastsql[$this->dbname] = "SHOW TABLE STATUS FROM " . $this->dbname;
			$tables = $this->dosql();
		} else {
			$tables[]["Name"] = $table;
		}
		foreach ($tables as $k => $value) {
			self::$lastsql[$this->dbname] = "SHOW FULL COLUMNS FROM " . $value["Name"];
			$result[$value["Name"]] = $value;
			$arr = $this->dosql();
			foreach ($arr as $s => $val) {
				$result[$value["Name"]]["table"][$val["Field"]] = $val;
			}
		}
		return $result;
	}

	//创建数据表
	public function repairTable($table, $data = array(), $repair = "") {
		$tableInfos = $this->showTables();
		if (!isset($tableInfos[$table])) {
			$sql = "CREATE TABLE `" . $table . "` ( `id` int(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (`id`),KEY `id` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$tableInfos = $this->showTables();
		}
		if (count($data) >= 1) {
			$sql = $this->creatField($table, $data, $tableInfos[$table]["table"]);
			self::$lastsql[$this->dbname] = $sql;
			$this->dosql();
			$tableInfos = $this->showTables();
		}
		return $tableInfos;
	}

	//创建数据库连接
	private function creatConnect($db_con = array()) {
		class_exists('PDO') or die("PDO: class not exists.");
		if (empty($db_con)) {echo "没有可使用的数据库连接数据";exit;}
		$this->dbname = $db_con["name"];
		if (is_null(self::$_act[$this->dbname])) {
			self::$_act[$this->dbname] = self::connect($db_con);
		}
		self::$prefix[$this->dbname] = $db_con["prefix"];
	}

	//建立数据库链接
	private static function connect($db_cons) {
		foreach ($db_cons as $k => $var) {${$k} = $var;}
		$dbhs = $type . ':host=' . $host . ';port=' . $port . ";dbname=" . $name;
		$option = $pconnect ? array(PDO::ATTR_PERSISTENT => true) : array();
		try {
			$dbh = new PDO($dbhs, $user, $pass, $option);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		} catch (PDOException $e) {
			$err = $e->getMessage();
		}
		$dbh->exec('SET NAMES utf8');
		return $dbh;
	}

	//执行sql语句
	protected function dosql() {
		$queryIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK';
		if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', self::$lastsql[$this->dbname])) {
			return self::$_act[$this->dbname]->exec(self::$lastsql[$this->dbname]);
		} else {
			$pdostmt = self::$_act[$this->dbname]->prepare(self::$lastsql[$this->dbname]);
			$pdostmt->execute();
			$result = $pdostmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		}
	}

	//创建表列
	protected static function creatField($table, $fileds, $infos) {
		$sql = "";
		$haveText = false;
		foreach ($fileds as $k => $filed) {
			if (!isset($infos[$k])) {
				if ($fileds["Type"] == "text") {$haveText = True;}
				if ($fileds["Null"] == "YES") {$node = "DEFAULT NULL";} else { $node = "NOT NULL";}
				$sql .= "ALTER TABLE `" . $table . "` ADD `" . $k . "` " . $filed["Type"] . " " . $node . " " . $filed["Extra"] . ";";
				if ($fileds["Key"] == 'PRI') {$sql .= "ALTER TABLE `" . $table . "` ADD PRIMARY KEY (`" . $k . "`);";}
				if ($fileds["Key"] == 'MUL') {$sql .= "ALTER TABLE `" . $table . "` ADD INDEX " . $k . " (`" . $k . "`);";}
				if ($fileds["Key"] == 'UNI') {$sql .= "ALTER TABLE `" . $table . "` ADD UNIQUE (`" . $k . "`);";}
			} else {
				foreach ($filed as $s => $value) {
					if ($value != $infos[$k][$s]) {
						$sql .= "ALTER TABLE `" . $table . "` MODIFY COLUMN " . $k . " " . $value . ";";
					}
				}
			}
		}
		if ($haveText) {
			$sql .= '
				ALTER TABLE ' . $table . ' PARTITION by RANGE(id)(
				PARTITION p0 VALUES less than (200000),
				PARTITION p1 VALUES less than (400000),
				PARTITION p2 VALUES less than (600000),
				PARTITION p8 VALUES less than MAXVALUE
				);
			';
		}
		return $sql;
	}

	//关闭数据库连接
	public function __destruct() {
		self::$_act = null;
	}
}
?>