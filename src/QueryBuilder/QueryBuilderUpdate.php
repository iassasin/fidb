<?php

namespace Iassasin\Fidb\QueryBuilder;

use Iassasin\Fidb\Connection\Connection;
use \Iassasin\Fidb\Statement;

class QueryBuilderUpdate {
	/** @var Connection */
	protected $db;

	/** @var array[] */
	protected $columns;
	/** @var string */
	protected $table;
	/** @var array[] */
	protected $where; // [[names], [values]]

	public function __construct(Connection $db) {
		$this->db = $db;
		$this->clear();
	}

	public function clear() {
		// [[name, val, template], ...]
		$this->columns = [];
		$this->table = '';
		$this->where = [[],[]];
	}

	public function sql(): string {
		$args = [$this->table];
		$stmtTpl = [];

		foreach ($this->columns as $col) {
			$stmtTpl[] = '%a = '.$col[2];
			$args[] = $col[0];
			$args[] = $col[1];
		}

		$sql = 'UPDATE %a SET '.join(',', $stmtTpl);

		if (count($this->where[0]) > 0) {
			$sql .= ' WHERE ('.join(') AND (', $this->where[0]).')';
			foreach ($this->where[1] as $arg) { $args[] = $arg; }
		}

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

	public function where(string $name, ...$vals): self {
		$this->where[0][] = $name;
		if (count($vals) > 0) {
			\array_push($this->where[1], ...$vals);
		}

		return $this;
	}
}
