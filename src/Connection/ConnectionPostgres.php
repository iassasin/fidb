<?php

namespace Iassasin\Fidb\Connection;

use \Iassasin\Fidb\QueryBuilder\QueryBuilderSelect;
use \Iassasin\Fidb\QueryBuilder\QueryBuilderSelectPostgres;

class ConnectionPostgres extends Connection {
	protected function makeConnection($host, $database, $user, $password): \PDO {
		return new \PDO("pgsql:host=$host;dbname=$database", $user, $password);
	}

	protected function quoteString($val): string {
		return $this->conn->quote($val);
	}

	protected function escapeString($val): string {
		return substr($this->conn->quote($val), 1, -1);
	}

	protected function quoteIdentifier($val): string {
		return '"'.$this->escapeString($val).'"';
	}

	public function lastInsertID() {
		$id = $this->execute('SELECT LASTVAL()');
		return $id !== false ? $id->result() : false;
	}

	public function select(): QueryBuilderSelect {
		return new QueryBuilderSelectPostgres($this);
	}
}
