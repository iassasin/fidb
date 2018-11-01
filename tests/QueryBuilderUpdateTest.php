<?php
/**
 * Author: iassasin <iassasin@yandex.ru>
 * License: beerware
 * Use for good
 */

namespace Iassasin\Fidb\Tests;

use Iassasin\Fidb\Connection\ConnectionMysql;
use Iassasin\Fidb\Connection\Connection;

class QueryBuilderUpdateTest extends \PHPUnit\Framework\TestCase {
	public function testQueryBuilder() {
		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));

		$this->assertTrue($conn instanceof Connection);

		$bu = $conn->update('testtable');

		$bu->column('col1', 's123');
		$bu->column('col2', 321);
		$bu->column('col3', '33', '%d');
		$bu->where('f1 > 0')
			->where('f2 > %d', 55)
			->where('%a = %s', 'f3', 'str');

		$this->assertEquals($bu->sql(), 'UPDATE `testtable` SET `col1` = "s123",`col2` = "321",`col3` = 33'
			.' WHERE (f1 > 0) AND (f2 > 55) AND (`f3` = "str")');
	}
}
