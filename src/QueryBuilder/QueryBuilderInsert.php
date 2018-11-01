<?php

namespace Iassasin\Fidb\QueryBuilder;

use Iassasin\Fidb\Connection\Connection;
use \Iassasin\Fidb\Statement;

class QueryBuilderInsert {
	/** @var Connection */
	protected $db;

	/** @var array[] */
	protected $columns;
	/** @var string */
	protected $table;

	public function __construct(Connection $db) {
		$this->db = $db;
		$this->clear();
	}

	public function clear() {
		// [[name, val, template], ...]
		$this->columns = [];
		$this->table = '';
	}

	public function sql(): string {
		$args = [$this->table];
		$colsTpl = [];
		$valsTpl = [];

		foreach ($this->columns as $col) {
			$colsTpl[] = '%a';
			$args[] = $col[0];
		}

		foreach ($this->columns as $col) {
			$valsTpl[] = $col[2];
			$args[] = $col[1];
		}

		$sql = 'INSERT INTO %a('.join(',', $colsTpl).') VALUES ('.join(',', $valsTpl).')';

		return $this->db->prepareQueryString($sql, $args);
	}

	/**
	 * Build and execute sql query
	 * @return Statement|bool Result statement
	 */
	public function execute() {
		return $this->db->execute($this->sql());
	}

	public function column(string $name, $val, string $template = '%s'): self {
		$this->columns[] = [$name, $val, $template];
		return $this;
	}

	public function table(string $name): self {
		$this->table = $name;
		return $this;
	}
}
