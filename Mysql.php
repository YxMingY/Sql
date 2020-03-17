<?php


namespace yxmingy;
require_once "Table.php";

use mysqli;

class Mysql extends mysqli {
	private $connected = false;
	private $stmt_error = "No Error";

	/**
	 * Mysql constructor.
	 * @param $host
	 * @param $user
	 * @param $pass
	 * @param $db
	 */
	public function __construct($host, $user, $pass, $db) {
		parent::__construct($host, $user, $pass, $db);
		if (!mysqli_connect_error())
			$this->connected = true;
	}

	/**
	 * Check if connect successfully.
	 * @return bool
	 */
	public function connected():bool
	{
		return $this->connected;
	}

	/**
	 * Used when connecting fail
	 * @return string
	 */
	public function getConnectError():string
	{
		return 'Connect Error ('.mysqli_connect_errno().') '. mysqli_connect_error();
	}

	/**
	 * Used when some query fail except safe-query
	 * note: safe-query should use getStmtError()
	 * safe-query list: insert() and selectById()
	 * @return string
	 */
	public function getError():string
	{
		return 'Mysql Error ('.$this->errno.') '. $this->error;
	}

	/**
	 * Used when some safe-query fail
	 * safe-query list: insert() and selectById()
	 * @return string
	 */
	public function getStmtError():string
	{
		return $this->stmt_error;
	}

	public function setUTF8():bool
	{
		return $this->query("set names utf8");
	}


	/**
	 * Warn: This action is not safe.
	 * @param string $table
	 * @param string $colomn
	 * @param string|NULL $where
	 * @return array|null
	 */
	public function select(string $table, string $colomn, string $where = NULL):?array
	{
		$rows = [];
		if($this->queryResult($res,"SELECT $colomn FROM $table".($where==NULL ? "" : " WHERE $where"))) {
			while ($row = $res->fetch_assoc()) {
				$rows[] = $row;
			}
			$res->free();
			return $rows;
		}else{
			return NULL;
		}
	}

	/**
	 * Safely select by one field equals specified one
	 * @param string $table
	 * @param string $colomn
	 * @param string $id_field_name
	 * @param $check_equal_value
	 * @return array|null
	 */
	public function selectById(string $table, string $colomn, string $id_field_name, $check_equal_value):?array
	{
		$rows = [];
		if($this->safeQueryResult($res,"SELECT $colomn FROM $table WHERE $id_field_name=?",is_int($check_equal_value) ? "i" : "s",$check_equal_value)) {
			while ($row = $res->fetch_assoc()) {
				$rows[] = $row;
			}
			$res->free();
			return $rows;
		}else{
			return NULL;
		}
	}

	/**
	 * Safely insert a row.
	 * @param string $table
	 * @param array $map - ["field_name" => "value"]
	 * @return bool
	 */
	public function insert(string $table, array $map):bool
	{
		$format = "";
		$marks = [];
		foreach ($map as $value) {
			$marks[] = "?";
			if(is_int($value)):
				$format .= "i";
			elseif(is_double($value)):
				$format .= "d";
			else:
				$format .= "s";
			endif;
		}
		return $this->_insert($table,implode(", ",array_keys($map)),implode(", ",$marks),$format,array_values($map));
	}
	public function show(string $what):?array
	{
		$rows = [];
		if($this->queryResult($res,"SHOW $what")) {
			while ($row = $res->fetch_row()) {
				$rows[] = $row;
			}
			$res->free();
			return $rows;
		}else{
			return NULL;
		}
	}
	public function showOne(string $what):?array
	{
		$rows = [];
		if($this->queryResult($res,"SHOW $what")) {
			while ($row = $res->fetch_row()) {
				$rows[] = $row[0];
			}
			$res->free();
			return $rows;
		}else{
			return NULL;
		}
	}
	public function update(string $table,string $where,string ...$settings):bool
	{
		return $this->query("UPDATE $table SET ".implode(", ",$settings)." WHERE $where");
	}
	public function delete(string $table,string $where):bool
	{
		return $this->query("DELETE FROM $table WHERE $where");
	}

	public function createDatabase(string $name):bool
	{
		return $this->query("CREATE DATABASE $name");
	}
	public function dropDatabase(string $name):bool
	{
		return $this->query("DROP DATABASE $name");
	}
	public function createTable(Table $table)
	{
		$res = $this->query("CREATE ".$table);
		unset($table);
		return $res;
	}
	public function dropTable(string $name):bool
	{
		return $this->query("DROP TABLE $name");
	}

	private function _insert(string $table,string $keys,string $values,string $format,array $params):bool
	{
		return $this->safeQuery("INSERT INTO $table ($keys) VALUES ($values)",$format,...$params);
	}

	/**
	 * Query and save the result (Unsafe)
	 * @param $result
	 * @param string $query
	 * @param int $resultmode
	 * @return bool
	 */
	public function queryResult(&$result, string $query, $resultmode = MYSQLI_STORE_RESULT):bool
	{
		$result = parent::query($query, $resultmode);
		return $result !== false;
	}

	/**
	 * Parameterized query (Safe)
	 * @param string $query
	 * @param string $format
	 * @param mixed ...$params
	 * @return bool
	 */
	public function safeQuery(string $query, string $format, ...$params):bool
	{
		$stmt = $this->stmt_init();
		if(!$stmt->prepare($query))
			return false;
		$stmt->bind_param($format,...$params);
		$state = $stmt->execute();
		if(!$state)
			$this->stmt_error = 'Mysql Error ('.$stmt->errno.') '. $stmt->error;
		$stmt->close();
		return $state;
	}

	/**
	 * Parameterized query and save the result (Safe)
	 * @param $result
	 * @param string $query
	 * @param string $format
	 * @param mixed ...$params
	 * @return bool
	 */
	public function safeQueryResult(&$result, string $query, string $format, ...$params):bool
	{
		$stmt = $this->stmt_init();
		if(!$stmt->prepare($query))
			return false;
		$stmt->bind_param($format,...$params);
		$state = $stmt->execute();
		$result = $stmt->get_result();
		if(!$state)
			$this->stmt_error = 'Mysql Error ('.$stmt->errno.') '. $stmt->error;
		$stmt->close();
		return $state;
	}
}


