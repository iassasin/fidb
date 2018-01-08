# Fine Interface to Database

Very simple and lightweight database connection wrapper making easer and safer to construct queries.

# Install
Use composer to install `fidb`:

```
composer require iassasin/fidb
```

# Usage
```php
require_once 'vendor/autoload.php';

use Iassasin\Fidb\Connection\ConnectionMysql;

$db = new ConnectionMysql($host, $database, $user, $password);

$db->query('INSERT INTO table(num, str, txt) VALUES(%d, %s, %s)',
	123, 'string', 'and "text"'); // automatic string escaping

// build queries of any complexity with variables
$bs = $db->select()
	->column('str')
	->table('table')
	->where('num > %d', 30);

foreach ($bs->execute()->fetchAll() as $row){
	echo $row['str'];
}
```

More examples see in [example](example/example.php)
