<?php

namespace Iassasin\Fidb\Connection;

use \Iassasin\Fidb\QueryBuilder\QueryBuilderSelect;
use \Iassasin\Fidb\Statement;

abstract class Connection {
	protected $conn;

	public function __construct($host, $database, $user, $password){
		$this->conn = $this->makeConnection($host, $database, $user, $password);
		$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function __destruct(){
		$this->close();
	}

	protected abstract function makeConnection($host, $database, $user, $password);
	protected abstract function quoteString($val);
	protected abstract function escapeString($val);
	protected abstract function quoteIdentifier($val);

	public function close(){
		if ($this->conn){
			$this->conn = false;
		}
	}

	public function execute($q){
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

	public function prepareQueryString($q, $args){
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

	public function query($q){
		$l = func_num_args();
		$args = func_get_args();
		array_shift($args);

		if ($l < 1)
			return null;

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

	public function prepare($q){
		$l = func_num_args();
		$args = func_get_args();
		array_shift($args);

		if ($l < 1)
			return null;

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

	public function select(){
		return new QueryBuilderSelect($this);
	}
}
