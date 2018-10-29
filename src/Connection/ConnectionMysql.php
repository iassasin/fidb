<?php

namespace Iassasin\Fidb\Connection;

use \Iassasin\Fidb\QueryBuilder\QueryBuilderSelect;
use \Iassasin\Fidb\QueryBuilder\QueryBuilderSelectMysql;

class ConnectionMysql extends Connection {
	protected function makeConnection($host, $database, $user, $password): \PDO {
		return new \PDO("mysql:host=$host;dbname=$database", $user, $password);
	}

	protected function quoteString($val): string {
		return $this->conn->quote($val);
	}

	protected function escapeString($val): string {
		return substr($this->conn->quote($val), 1, -1);
	}

	protected function quoteIdentifier($val): string {
		return '`'.$this->escapeString($val).'`';
	}

	public function foundRows(): int {
		$cnt = $this->execute("SELECT FOUND_ROWS()");
		return $cnt !== false ? $cnt->result() : 0;
	}

	public function lastInsertID() {
		$id = $this->execute('SELECT LAST_INSERT_ID()');
		return $id !== false ? $id->result() : false;
	}

	public function select(): QueryBuilderSelect {
		return new QueryBuilderSelectMysql($this);
	}
}
