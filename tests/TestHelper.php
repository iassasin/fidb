<?php

namespace Iassasin\Fidb\Tests;

use Iassasin\Fidb\Connection\ConnectionMysql;
use Iassasin\Fidb\Connection\Connection;

class TestHelper {
	public static function createPdoMock($tester): \PDO {
		$pdoMock = $tester->getMockBuilder(\PDO::class)
			->disableOriginalConstructor()
			->disableOriginalClone()
			->getMock();

		$pdoMock->method('quote')
			->will($tester->returnCallback(function($str) { return '"'.$str.'"'; }));

		return $pdoMock;
	}

	public static function createConnectionMock($tester, $pdoMock): Connection {
		$conn = $tester->getMockBuilder(ConnectionMysql::class)
			->setMethods(['makeConnection'])
			->disableOriginalConstructor()
			->getMock();

		$conn->method('makeConnection')
			->willReturn($pdoMock);

		$conn->__construct('', '', '', '');

		return $conn;
	}
}
