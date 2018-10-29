<?php

namespace Iassasin\Fidb;

class Statement {
	/**
	 * @var \PDOStatement
	 */
	private $res;

	public function __construct(\PDOStatement $rs) {
		$this->res = $rs;
		$rs->setFetchMode(\PDO::FETCH_ASSOC);
	}

	public function __destruct(){ $this->free(); }

	public function rows(): int {
		return $this->res->rowCount();
	}

	public function fields(): int {
		return $this->res->columnCount();
	}

	public function fetch(): array {
		return $this->res->fetch();
	}

	public function fetchObject(string $class) {
		return $this->createObjectWithValues($this->fetch(), $class);
	}

	public function fetchAll(): array {
		$res = [];
		while ($row = $this->fetch()){
			$res[] = $row;
		}
		return $res;
	}

	public function fetchAllObjects(string $class): array {
		$res = [];
		while ($row = $this->fetchObject($class)){
			$res[] = $row;
		}
		return $res;
	}

	public function result($col = 0) {
		return $this->res->fetchColumn($col);
	}

	public function free() {
		if ($this->res !== null){
			$this->res = null;
			return true;
		}
		return false;
	}

	public function execute(array $args): bool {
		return $this->res->execute($args);
	}

	private function createObjectWithValues(array $values, string $class) {
		$ref = new \ReflectionClass($class);
		$obj = $ref->newInstance();

		foreach ($ref->getProperties() as $prop) {
			$name = $prop->getName();
			if (array_key_exists($name, $values)) {
				$prop->setAccessible(true);
				$prop->setValue($obj, $values[$name]);
			}
		}

		return $obj;
	}
}
