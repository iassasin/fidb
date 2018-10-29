<?php

namespace Iassasin\Fidb\QueryBuilder;

class QueryBuilderSelectMysql extends QueryBuilderSelect {
	protected $funcCalcFoundRows;

	public function clear() {
		parent::clear();
		$this->funcCalcFoundRows = false;
	}

	public function sql(): string {
		$sql = parent::sql();

		if ($this->funcCalcFoundRows){
			$sql = 'SELECT SQL_CALC_FOUND_ROWS'.substr($sql, 6);
		}

		return $sql;
	}
}
