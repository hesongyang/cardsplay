<?php

class Mysql
{
	public $conn;

	function __construct($hostname, $username, $password, $dbname, $charset = "utf8mb4")
	{
		$conn = mysqli_connect($hostname, $username, $password);
		if (!$conn) {
			echo '连接失败，请联系管理员';
			exit;
		}
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->dbname = $dbname;
		$this->conn = $conn;
		$res = mysqli_select_db($this->conn, $dbname);
		if (!$res) {
			echo '连接失败，请联系管理员';
			exit;
		}
		mysqli_set_charset($this->conn, $charset);
	}

	function __destruct()
	{
	}

	function getAll($sql)
	{
		$this->conn = mysqli_connect($this->hostname, $this->username, $this->password);
		mysqli_select_db($this->conn, $this->dbname);
		mysqli_set_charset($this->conn, "utf8");
		$result = mysqli_query($this->conn, $sql);
		$data = array();
		if ($result && mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$data[] = $row;
			}
		}
		return $data;
	}

	function getOne($sql)
	{
		$this->conn = mysqli_connect($this->hostname, $this->username, $this->password);
		mysqli_select_db($this->conn, $this->dbname);
		mysqli_set_charset($this->conn, "utf8mb4");
		$result = mysqli_query($this->conn, $sql);
		$data = array();
		if ($result && mysqli_num_rows($result) > 0) {
			$data = mysqli_fetch_assoc($result);
		}
		return $data;
	}

	function insert($table, $data)
	{
		$this->conn = mysqli_connect($this->hostname, $this->username, $this->password);
		mysqli_select_db($this->conn, $this->dbname);
		mysqli_set_charset($this->conn, "utf8mb4");
		$tmpdata = [];
		foreach ($data as $k => $v) {
			$tmpdata[$this->s($k)] = $this->s($v);
		}
		$data = $tmpdata;
		$str = '';
		$str .= "INSERT INTO `$table` ";
		$str .= "(`" . implode("`,`", array_keys($data)) . "`) ";
		$str .= " VALUES ";
		$str .= "('" . implode("','", $data) . "')";
		$res = mysqli_query($this->conn, $str);
		if ($res && mysqli_affected_rows($this->conn) > 0) {
			return mysqli_insert_id($this->conn);
		} else {
			return false;
		}
	}

	function update($table, $data, $where)
	{
		$this->conn = mysqli_connect($this->hostname, $this->username, $this->password);
		mysqli_select_db($this->conn, $this->dbname);
		mysqli_set_charset($this->conn, "utf8mb4");
		$sql = 'UPDATE ' . $table . ' SET ';
		foreach ($data as $key => $value) {
			$key = $this->s($key);
			$value = $this->s($value);
			$sql .= "`{$key}`='{$value}',";
		}
		$sql = rtrim($sql, ',');
		$sql .= " WHERE $where";
		$res = mysqli_query($this->conn, $sql);
		if ($res && mysqli_affected_rows($this->conn)) {
			return mysqli_affected_rows($this->conn);
		} else {
			return false;
		}
	}

	function del($table, $where)
	{
		$this->conn = mysqli_connect($this->hostname, $this->username, $this->password);
		mysqli_select_db($this->conn, $this->dbname);
		mysqli_set_charset($this->conn, "utf8mb4");
		$sql = "DELETE FROM `{$table}` WHERE {$where}";
		$res = mysqli_query($this->conn, $sql);
		if ($res && mysqli_affected_rows($this->conn)) {
			return mysqli_affected_rows($this->conn);
		} else {
			return false;
		}
	}

	function s($str)
	{
		return mysqli_real_escape_string($this->conn, $str);
	}
}