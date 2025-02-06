<?php

namespace Iassasin\Fidb\QueryBuilder;

class QueryBuilderSelectPostgres extends QueryBuilderSelect {
	public function limit(int $from, int $max): self {
		$this->limit[0] = 'LIMIT %d OFFSET %d';
		$this->limit[1] = [];
		$this->processVals($this->limit[1], [$max, $from]);
		return $this;
	}
}
