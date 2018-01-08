<?php

namespace Iassasin\Fidb;

class Statement {
	private $res;

	public function __construct($rs){
		$this->res = $rs;
		$rs->setFetchMode(\PDO::FETCH_ASSOC);
	}

	public function __destruct(){ $this->free(); }

	public function rows(){
		return $this->res->rowCount();
	}

	public function fields(){
		return $this->res->columnCount();
	}

	public function fetch(){
		return $this->res->fetch();
	}

	public function fetchAll(){
		$res = array();
		while ($row = $this->fetch()){
			$res[] = $row;
		}
		return $res;
	}

	public function result($col = 0){
		return $this->res->fetchColumn($col);
	}

	public function free(){
		if ($this->res !== null){
			$this->res = null;
			return true;
		}
		return false;
	}

	public function execute(array $args){
		return $this->res->execute($args);
	}
}
