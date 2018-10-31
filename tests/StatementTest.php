<?php

namespace Iassasin\Fidb\Tests\StatementTest;

class Data {
	private $private;
	protected $protected;
	public $public;

	public function getPrivate() { return $this->private; }
	public function getProtected() { return $this->protected; }
}

namespace Iassasin\Fidb\Tests;

use Iassasin\Fidb\Statement;
use Iassasin\Fidb\Tests\StatementTest\Data;

class StatementTest extends \PHPUnit\Framework\TestCase {
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
}
