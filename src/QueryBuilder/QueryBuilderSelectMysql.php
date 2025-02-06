<?php

namespace Iassasin\Fidb\QueryBuilder;

class QueryBuilderSelectMysql extends QueryBuilderSelect {
	protected $funcCalcFoundRows;

	public function calcFoundRows($val = true): self {
		$this->funcCalcFoundRows = $val;
		return $this;
	}

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

	public function count() {
		$calc = $this->funcCalcFoundRows;
		$this->funcCalcFoundRows = false;

		$res = parent::count();

		$this->funcCalcFoundRows = $calc;

		return $res;
	}
}
