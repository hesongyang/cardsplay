<?php
namespace Common\Model;

use Think\Model;

class CommonModel extends Model
{
	final public function drop_table($tablename)
	{
		$tablename = C("DB_PREFIX") . $tablename;
		return $this->query("DROP TABLE $tablename");
	}

	final public function list_tables()
	{
		$tables = array();
		$data = $this->query("SHOW TABLES");
		foreach ($data as $k => $v) {
			$tables[] = $v['tables_in_' . strtolower(C("DB_NAME"))];
		}
		return $tables;
	}

	final public function table_exists($table)
	{
		$tables = $this->list_tables();
		return in_array(C("DB_PREFIX") . $table, $tables) ? true : false;
	}

	final public function get_fields($table)
	{
		$fields = array();
		$table = C("DB_PREFIX") . $table;
		$data = $this->query("SHOW COLUMNS FROM $table");
		foreach ($data as $v) {
			$fields[$v['Field']] = $v['Type'];
		}
		return $fields;
	}

	final public function field_exists($table, $field)
	{
		$fields = $this->get_fields($table);
		return array_key_exists($field, $fields);
	}

	protected function _before_write(&$data)
	{
	}
} 