<?php

require_once '../vendor/autoload.php';

use Iassasin\Fidb\Connection\ConnectionMysql;

$host = 'localhost';
$database = 'test';
$user = 'test';
$password = '';
$table = 'my-test-table';

$db = new ConnectionMysql($host, $database, $user, $password);

$db->query('DROP TABLE IF EXISTS %a', $table);
$db->query('CREATE TABLE %a(n int, s varchar(60))', $table);

$db->query('INSERT INTO %a(n, s) VALUES(%d, %s)', $table, 1, 'a');

$ps = $db->prepare('INSERT INTO %a(n, s) VALUES(?, ?)', $table);
$ps->execute([2, 'b']);
$ps->execute([3, 'c']);
$ps->execute([4, 'd']);

$rs = $db->query('SELECT * FROM %a', $table);
echo "[n, s]\n";
while ($row = $rs->fetch()){
	echo "${row['n']}, ${row['s']}\n";
}

$bs = $db->select()
	->column('t1.n AS n1, t2.n AS n2, t1.s AS s1, t2.s AS s2')
	->table('%a t1', $table)
	->join('%a t2', 't1.n = t2.n', $table)
	->where('t1.%a > %d', ['n', 2]); //multiple arguments

echo "\n\nSql: ".$bs->sql()."\n";
echo "[t1.n, t2.n, t1.s, t2.s]\n";
foreach ($bs->execute()->fetchAll() as $row){
	echo "${row['n1']}, ${row['n2']}, ${row['s1']}, ${row['s2']}\n";
}
