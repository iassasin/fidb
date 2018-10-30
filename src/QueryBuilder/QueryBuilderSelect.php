<?php

namespace Iassasin\Fidb\QueryBuilder;

use \Iassasin\Fidb\Connection\Connection;

class QueryBuilderSelect {
	/** @var Connection */
	protected $db;

	protected $columns;
	protected $tables;
	protected $joins;
	protected $where;
	protected $group;
	protected $having;
	protected $order;
	protected $limit;

	public function __construct(Connection $db) {
		$this->db = $db;
		$this->clear();
	}

	protected function processVals(&$arr, $vals) {
		if ($vals !== null){
			if (is_array($vals)){
				foreach ($vals as $val){
					$arr[] = $val;
				}
			} else {
				$arr[] = $vals;
			}
		}
	}

	public function sql(): string {
		$args = [];
		$sql = 'SELECT ';

		$sql .= join(', ', $this->columns[0]).' ';
		foreach ($this->columns[1] as $arg){ $args[] = $arg; }

		$sql .= 'FROM '.join(', ', $this->tables[0]).' ';
		foreach ($this->tables[1] as $arg){ $args[] = $arg; }

		foreach ($this->joins[0] as $j){
			$sql .= $j[0].' JOIN '.$j[1].' ON '.$j[2].' ';
		}
		foreach ($this->joins[1] as $arg){ $args[] = $arg; }

		if (count($this->where[0]) > 0){
			$sql .= 'WHERE ('.join(') AND (', $this->where[0]).') ';
			foreach ($this->where[1] as $arg){ $args[] = $arg; }
		}

		if (count($this->group[0]) > 0){
			$sql .= 'GROUP BY '.join(', ', $this->group[0]).' ';
			foreach ($this->group[1] as $arg){ $args[] = $arg; }
		}

		if (count($this->having[0]) > 0){
			$sql .= 'HAVING ('.join(') AND (', $this->having[0]).') ';
			foreach ($this->having[1] as $arg){ $args[] = $arg; }
		}

		if (count($this->order[0]) > 0){
			$sql .= 'ORDER BY '.join(', ', $this->order[0]).' ';
			foreach ($this->order[1] as $arg){ $args[] = $arg; }
		}

		if ($this->limit[0] != ''){
			$sql .= $this->limit[0].' ';
			foreach ($this->limit[1] as $arg){ $args[] = $arg; }
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

	public function clear() {
		//[[names],[args]]
		$this->columns = [[],[]];
		$this->tables = [[], []];
		$this->joins = [[], []]; // names = [join_type, table_name, cond]
		$this->where = [[],[]];
		$this->having = [[],[]];
		$this->group = [[],[]];
		$this->order = [[],[]];
		$this->limit = ['',[]];
	}

	public function column(string $name, $vals = null): self {
		$this->columns[0][] = $name;
		$this->processVals($this->columns[1], $vals);
		return $this;
	}

	public function table(string $name, $vals = null): self {
		$this->tables[0][] = $name;
		$this->processVals($this->tables[1], $vals);
		return $this;
	}

	public function join(string $name, string $cond, $vals = null, string $dir = 'LEFT'): self {
		$this->joins[0][] = [$dir, $name, $cond];
		$this->processVals($this->joins[1], $vals);
		return $this;

		$this->tables[] = $name;
		$this->where[0][] = $cond;
		$this->processVals($this->where[1], $vals);
		return $this;
	}

	protected function outerJoin(string $name, string $cond, $vals = null, string $dir): self {
		$this->joins[0][] = [$dir.' OUTER', $name, $cond];
		$this->processVals($this->joins[1], $vals);
		return $this;
	}

	public function leftOuterJoin(string $name, string $cond, $vals = null): self {
		return $this->outerJoin($name, $cond, $vals, 'LEFT');
	}

	public function rightOuterJoin(string $name, string $cond, $vals = null): self {
		return $this->outerJoin($name, $cond, $vals, 'RIGHT');
	}

	public function where(string $name, $vals = null): self {
		$this->where[0][] = $name;
		$this->processVals($this->where[1], $vals);
		return $this;
	}

	public function having(string $name, $vals = null): self {
		$this->having[0][] = $name;
		$this->processVals($this->having[1], $vals);
		return $this;
	}

	public function group(string $name, $vals = null): self {
		$this->group[0][] = $name;
		$this->processVals($this->group[1], $vals);
		return $this;
	}

	public function order(string $name, $vals = null): self {
		$this->order[0][] = $name;
		$this->processVals($this->order[1], $vals);
		return $this;
	}

	public function limit(int $from, int $max): self {
		$this->limit[0] = 'LIMIT %d, %d';
		$this->limit[1] = [];
		$this->processVals($this->limit[1], [$from, $max]);
		return $this;
	}
}
