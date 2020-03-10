<?php


namespace yxmingy;
require_once "Table.php";

use mysqli;

class Mysql extends mysqli {
	private $connected = false;
	public function __construct($host, $user, $pass, $db) {
		parent::__construct($host, $user, $pass, $db);

		if (!mysqli_connect_error())
			$this->connected = true;
	}
	public function connected():bool
	{
		return $this->connected;
	}
	public function getConnectError():string
	{
		return 'Connect Error ('.mysqli_connect_errno().') '. mysqli_connect_error();
	}
	public function setUTF8():bool
	{
		return $this->query("set names utf8");
	}
	public function q_query(&$result,string $query, $resultmode = MYSQLI_STORE_RESULT):bool
	{
		$result = parent::query($query, $resultmode);
		return $result !== false;
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
		$res = $this->query("CREATE".$table);
		unset($table);
		return $res;
	}
	public function dropTable(string $name):bool
	{
		return $this->query("DROP TABLE $name");
	}
	public function select(string $table, string $colomn, string $where = NULL):?array
	{
		$rows = [];
		if($this->q_query($res,"SELECT $colomn FROM $table".($where==NULL ? "" : " WHERE $where"))) {
			while ($row = $res->fetch_assoc()) {
				$rows[] = $row;
			}
			$res->free();
			return $rows;
		}else{
			return NULL;
		}
	}
	public function insert(string $table,string $keys,string $values):bool
	{
		return $this->query("INSERT INTO $table ($keys) VALUES ($values)");
	}
	public function a_insert(string $table,array $keys,array $values):bool
	{
		return $this->insert($table,implode(" ",$keys),implode(" ",$values));
	}
	public function show(string $what):?array
	{
		$rows = [];
		if($this->q_query($res,"SHOW $what")) {
			while ($row = $res->fetch_row()) {
				$rows[] = $row;
			}
			$res->free();
			return $rows;
		}else{
			return NULL;
		}
	}
	public function showSingle(string $what):?array
	{
		$rows = [];
		if($this->q_query($res,"SHOW $what")) {
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
}


