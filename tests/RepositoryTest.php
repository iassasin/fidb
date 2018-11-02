<?php

namespace Iassasin\Fidb\Tests\RepositoryTest;

use Iassasin\Fidb\Repository\BaseRepository;
use Iassasin\Fidb\Connection\Connection;

class Data {
	/** @Id */
	private $private;
	protected $protected;
	public $public;

	public static function of($private, $protected, $public) {
		$data = new static();
		$data->private = $private;
		$data->protected = $protected;
		$data->public = $public;
		return $data;
	}

	public function getPrivate() { return $this->private; }
	public function getProtected() { return $this->protected; }
}

class DataRepository extends BaseRepository {
	public function __construct(Connection $conn) {
		parent::__construct($conn, Data::class, 'data');
	}

	public function findByIdSql($id): string {
		return $this->selectBuilder()->where('%a = %s', [$this->getIdField(), $id])->sql();
	}
}

namespace Iassasin\Fidb\Tests;

use Iassasin\Fidb\Statement;
use Iassasin\Fidb\Tests\RepositoryTest\{Data, DataRepository};

class RepositoryTest extends \PHPUnit\Framework\TestCase {
	public function testObjectsFetch() {
		$expectedResult = [
			[
				'private' => 'priv',
				'protected' => 'prot',
				'public' => 'pub',
			],
			[
				'private' => 'priv2',
				'protected' => 'prot2',
				'public' => 'pub2',
			],
		];

		$mi = 0;
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->method('fetch')->will($this->returnCallback(function () use (&$mi, $expectedResult) {
			if ($mi < count($expectedResult)) {
				return $expectedResult[$mi++];
			}

			return false;
		}));

		$statement = new Statement($pdoStatement);

		$result = $statement->fetchAllObjects(Data::class);
		for ($i = 0; $i < count($expectedResult); ++$i) {
			$this->assertInstanceOf(Data::class, $result[$i]);
			$this->assertEquals($expectedResult[$i]['private'], $result[$i]->getPrivate());
			$this->assertEquals($expectedResult[$i]['protected'], $result[$i]->getProtected());
			$this->assertEquals($expectedResult[$i]['public'], $result[$i]->public);
		}
	}

	public function testFindObject() {
		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));

		$repo = new DataRepository($conn);
		$sql = $repo->findByIdSql('1');

		$this->assertEquals('SELECT private,protected,public FROM data WHERE (`private` = "1")', $sql);
	}

	public function testSaveInsertObject() {
		$expectedId = '1';

		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));

		$conn->method('lastInsertID')
			->willReturn($expectedId);

		$conn->expects($this->once())
			->method('execute')
			->with($this->equalTo('INSERT INTO `data`(`protected`,`public`) VALUES ("b","c")'))
			->willReturn(true);

		$repo = new DataRepository($conn);
		$data = Data::of(null, 'b', 'c');

		$savedData = $repo->save($data);

		$this->assertInstanceOf(Data::class, $savedData);
		$this->assertEquals($expectedId, $savedData->getPrivate());
		$this->assertEquals('b', $savedData->getProtected());
		$this->assertEquals('c', $savedData->public);
	}

	public function testSaveUpdateObject() {
		$expectedId = '1';

		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));

		$conn->method('lastInsertID')
			->willReturn('0');

		$conn->expects($this->once())
			->method('execute')
			->with($this->equalTo('UPDATE `data` SET `protected` = "b",`public` = "c" WHERE (`private` = "1")'))
			->willReturn(true);

		$repo = new DataRepository($conn);
		$data = Data::of($expectedId, 'b', 'c');

		$savedData = $repo->save($data);

		$this->assertInstanceOf(Data::class, $savedData);
		$this->assertEquals($expectedId, $savedData->getPrivate());
		$this->assertEquals('b', $savedData->getProtected());
		$this->assertEquals('c', $savedData->public);
	}
}
