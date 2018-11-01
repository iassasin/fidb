<?php
/**
 * Author: iassasin <iassasin@yandex.ru>
 * License: beerware
 * Use for good
 */

namespace Iassasin\Fidb\Tests;

use Iassasin\Fidb\Connection\ConnectionMysql;
use Iassasin\Fidb\Connection\Connection;

class QueryBuilderInsertTest extends \PHPUnit\Framework\TestCase {
	public function testQueryBuilder() {
		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));

		$this->assertTrue($conn instanceof Connection);

		$bi = $conn->insert('testtable');

		$bi->column('col1', 's123');
		$bi->column('col2', 321);
		$bi->column('col3', '33', '%d');

		$this->assertEquals($bi->sql(), 'INSERT INTO `testtable`(`col1`,`col2`,`col3`) VALUES ("s123","321",33)');
	}
}
