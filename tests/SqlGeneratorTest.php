<?php
/**
 * Author: iassasin <iassasin@yandex.ru>
 * License: beerware
 * Use for good
 */

namespace Iassasin\Fidb\Tests;

use Iassasin\Fidb\Connection\ConnectionMysql;
use Iassasin\Fidb\Connection\Connection;

class SqlGeneratorTest extends \PHPUnit\Framework\TestCase {
	public function testSimpleInterpolation() {
		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));
		$res = $conn->prepareQueryString('SELECT %a FROM %a WHERE id = %d AND d = %s', ['col', 'table', '1', 'a str']);
		$this->assertEquals('SELECT `col` FROM `table` WHERE id = 1 AND d = "a str"', $res);
	}
}
