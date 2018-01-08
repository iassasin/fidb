<?php

namespace Iassasin\Fidb\Connection;

use \Iassasin\Fidb\QueryBuilder\QueryBuilderSelectMysql;

class ConnectionMysql extends Connection {
	protected function makeConnection($host, $database, $user, $password){
		return new \PDO("mysql:host=$host;dbname=$database", $user, $password);
	}

	protected function quoteString($val){
		return $this->conn->quote($val);
	}

	protected function escapeString($val){
		return substr($this->conn->quote($val), 1, -1);
	}

	protected function quoteIdentifier($val){
		return '`'.$this->escapeString($val).'`';
	}

	public function foundRows(){
		$cnt = $this->q("SELECT FOUND_ROWS()");
		return $cnt !== false ? $cnt->result() : 0;
	}

	public function lastInsertID(){
		$id = $this->q('SELECT LAST_INSERT_ID()');
		return $id !== false ? $id->result() : false;
	}

	public function select(){
		return new QueryBuilderSelectMysql($this);
	}
}
