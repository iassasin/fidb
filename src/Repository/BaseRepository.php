<?php

namespace Iassasin\Fidb\Repository;

use Iassasin\Fidb\Connection\Connection;
use Iassasin\Fidb\QueryBuilder\QueryBuilderSelect;

abstract class BaseRepository {
	/** @var string */
	private $class;
	/** @var string */
	private $table;
	/** @var Connection */
	private $connection;
	/** @var \ReflectionProperty[] */
	private $fields;
	/** @var string[] */
	private $fieldNames;
	/** @var \ReflectionProperty */
	private $idField;

	protected function __construct(Connection $connection, string $class, string $table) {
		$this->class = $class;
		$this->table = $table;
		$this->connection = $connection;
		$this->idField = null;
		$this->detectModelFields();
	}

	private function detectModelFields() {
		$ref = new \ReflectionClass($this->class);
		$this->fields = $ref->getProperties();
		$names = [];

		foreach ($this->fields as $prop) {
			$prop->setAccessible(true);
			$name = $prop->getName();

			if ($name === 'id' && $this->idField === null) {
				$this->idField = $prop;
			}

			if (\preg_match('/@Id(?=\s|\*\/|$)/', $prop->getDocComment()) === 1) {
				$this->idField = $prop;
			}

			$names[] = $name;
		}

		if ($this->idField === null) {
			throw new \RuntimeException($this->class.' must provide an id field, did you forgot @Id annotation?');
		}

		$this->fieldNames = $names;
	}

	protected function getModelFields(): array {
		return $this->fieldNames;
	}

	protected function getClass(): string {
		return $this->class;
	}

	protected function getTable(): string {
		return $this->table;
	}

	protected function getConnection(): Connection {
		return $this->connection;
	}

	protected function getIdField(): string {
		return $this->idField->getName();
	}

	protected function selectBuilder(): QueryBuilderSelect {
		return $this->connection->select()
				->table($this->table)
				->column(join(',', $this->fieldNames));
	}

	/**
	 * Save object to repository
	 * @param  mixed $obj Object of correct type to save
	 * @return mixed|bool Saved object or false if save fails
	 */
	public function save($obj) {
		if (!($obj instanceof $this->class)) {
			throw new \RuntimeException('Object must be instance of class '.$this->class);
		}

		$id = $this->idField->getValue($obj);
		if ($id !== null) {
			return $this->updateRow($id, $obj);
		} else {
			return $this->insertRow($obj);
		}
	}

	private function updateRow($id, $obj) {
		$builder = $this->connection->update($this->table);
		$builder->where('%a = %s', $this->idField->getName(), $id);

		foreach ($this->fields as $field) {
			if ($field !== $this->idField) {
				$builder->column($field->getName(), $field->getValue($obj));
			}
		}

		if ($builder->execute()) {
			return $obj;
		} else {
			return false;
		}
	}

	private function insertRow($obj) {
		$builder = $this->connection->insert($this->table);

		foreach ($this->fields as $field) {
			if ($field !== $this->idField) {
				$value = $field->getValue($obj);
				if ($value !== null) {
					$builder->column($field->getName(), $value);
				}
			}
		}

		if ($builder->execute()) {
			$id = $this->connection->lastInsertID();
			$this->idField->setValue($obj, $id);
			return $obj;
		} else {
			return false;
		}
	}
}
