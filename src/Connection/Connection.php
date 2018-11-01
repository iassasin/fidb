<?php

namespace Iassasin\Fidb\Connection;

use \Iassasin\Fidb\QueryBuilder\{QueryBuilderSelect, QueryBuilderInsert};
use \Iassasin\Fidb\Statement;

abstract class Connection {
	protected $conn;

	public function __construct($host, $database, $user, $password) {
		$this->conn = $this->makeConnection($host, $database, $user, $password);
		$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function __destruct() {
		$this->close();
	}

	protected abstract function makeConnection($host, $database, $user, $password): \PDO;
	protected abstract function quoteString($val): string;
	protected abstract function escapeString($val): string;
	protected abstract function quoteIdentifier($val): string;

	public function close() {
		if ($this->conn){
			$this->conn = false;
		}
	}

	/**
	 * Execute raw sql query
	 * @param string $q query
	 * @return Statement|bool Result statement
	 */
	public function execute(string $q) {
		$res = $this->conn->query($q);

		if ($res === false){
			return false;
//			throw new \Exception("DataBase query error");
		}
		if ($res === true){
			return true;
		}
		return new Statement($res);
	}

	public function prepareQueryString(string $q, array $args): string {
		$l = count($args);

		$a = 0;
		$r = '';
		$p = 0;
		while (($p = strpos($q, '%', $p)) !== false){
			switch ($q[$p + 1]){
				case 'i':
				case 'd':
					$r = ''.+$args[$a++];
					break;

				case 's':
					$r = $this->quoteString($args[$a++]);
					break;

				case 'r':
					$r = $this->escapeString($args[$a++]);
					break;

				case 'a':
					$r = $this->quoteIdentifier($args[$a++]);
					break;

				case '%':
					$r = '%';
					break;

				case '-':
					$r = '';
					++$a;
					break;

				default:
					++$p;
					continue 2;
			}
			$q = substr_replace($q, $r, $p, 2);
			$p += strlen($r);
		}

		return $q;
	}

	/**
	 * Substitute sql template with args and execute a query
	 * @param string $q query template
	 * @param mixed $args,... arguments
	 * @return Statement|bool Result statement
	 */
	public function query(string $q, ...$args){
		$q = $this->prepareQueryString($q, $args);

		$res = $this->conn->query($q);

		if ($res === false){
			return false;
//			throw new \Exception("DataBase query error");
		}
		if ($res === true){
			return true;
		}
		return new Statement($res);
	}

	/**
	 * Substitute sql template with args and prepare a query
	 * @param string $q query template
	 * @param mixed $args,... arguments
	 * @return Statement|bool Prepared statement
	 */
	public function prepare($q, ...$args){
		$q = $this->prepareQueryString($q, $args);

		$res = $this->conn->prepare($q);

		if ($res === false){
			return false;
//			throw new \Exception("DataBase query error");
		}
		if ($res === true){
			return true;
		}
		return new Statement($res);
	}

	public function select(): QueryBuilderSelect {
		return new QueryBuilderSelect($this);
	}

	public function insert(string $table = ''): QueryBuilderInsert {
		return (new QueryBuilderInsert($this))->table($table);
	}
}
