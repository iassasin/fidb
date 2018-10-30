<?php
/**
 * Author: iassasin <iassasin@yandex.ru>
 * License: beerware
 * Use for good
 */

namespace Iassasin\Fidb\Tests;

use Iassasin\Fidb\Connection\ConnectionMysql;
use Iassasin\Fidb\Connection\Connection;

class SelectQueryBuilderTest extends \PHPUnit\Framework\TestCase {
	public function testQueryBuilder() {
		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));
		$bs = $conn->select();

		$bs->column('b.id, b.author_id');
		$bs->column('b.%a, qb.name AS qb_name', 'qid');
		$bs->table('bas b');
		$bs->join('qbas qb', 'b.qid = qb.id');
		$bs->where('b.del = %d', 0);
		$bs->where('qb.%a = %d', ['private', 0]);
		$bs->where('b.time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL %d DAY)', [7]);
		$bs->order('rating DESC, time ASC');
		$bs->limit(0, 5);

		$this->assertEquals($bs->sql(), 'SELECT b.id, b.author_id, b.`qid`, qb.name AS qb_name FROM bas b'
			.' LEFT JOIN qbas qb ON b.qid = qb.id WHERE (b.del = 0) AND (qb.`private` = 0)'
			.' AND (b.time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY)) ORDER BY rating DESC, time ASC LIMIT 0, 5 ');
	}

	public function testMysqlCalcFoundRows() {
		$conn = TestHelper::createConnectionMock($this, TestHelper::createPdoMock($this));
		$bs = $conn->select();

		$bs->column('a, b')
			->table('test')
			->having('a = b')
			->group('a')
			->calcFoundRows();

		$this->assertEquals($bs->sql(), 'SELECT SQL_CALC_FOUND_ROWS a, b FROM test GROUP BY a HAVING (a = b) ');
	}
}
