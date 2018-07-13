# Core\Database

> ต้องสร้าง `Core\App` เพื่อเป็น wrapper ให้ framework ด้วย จะได้ไม่ต้องใช้ static class มาก

## Query Builder
### Intentions
- Build any SQL statement string for later usage.
- Just return statement string

### Requirements
- Build any SQL statement with method-chaining technique intuitively.

### Architecture
```php
// BaseKeyword data structure
$select = [
    'input' => [$input],
    'keywords' => null,
    'clauses' => ['from', 'where', 'join', 'leftJoin', 'rightJoin', 'limit', 'orderBy', 'groupBy'],
    'previouseKeywords' => false // false => Must be first keyword; null => whatever!; [] => array of previous keywords
    'nextKeywords' => null
]

// ClauseKeyword data structure
$from = [
    'input' => [$input],
    'belongsTo' => ['select']
]
```

```
- Core\Database\Query
    - Keywords // Traits
        - SelectKeyword.php
        - InsertKeyword.php
        - UpdateKeyword.php
    - Builder.php // Abstract
```


### Usage
```php
$qb = new QueryBuilder();

// Store clauses and input values in the instance.
$qb->select('*')
        ->from('table_a')
        ->where('col_b', '=', 1220)
    ->leftJoin('table_b')
        ->on('table_a.col_b', '=', 'table_b.col_b')
        ->orOn('table_a.col_c', '<', 'table_b.col_c');

// Get statement
$select = $qb->get();

// Get prepared statement from stored clauses
$selectPrepared = $qb->getPrepared();

// Get stored input values
$selectValues = $qb->values();

// Reset instance, like instantiating a new one
$qb->clear();

// Union
$union = $qb->union(
    function($builder) {
        return $builder->select('*')->from('table_a');
    },
    function($builder) {
        return $builder->select('*')->from('table_b');
    }
);

$insert = $qb->insert([
                    'column_a' => 240, 
                    'column_b' => 730
                ])->into('table_a');

$qb->clear();

$update = $qb->update('table_a')
             ->set([
                 'column_a' => 1100,
                 'column_b' => 720
             ]);

$qb->clear();

$delete = $qb->delete('column_a')
             ->from('table_a')
             ->where('column_a', '>', 300);
```

## Database Connection
### Intentions
- Connect database to application.

### Usage
```php
$db = new DatabaseBridge();

// Prepare sql statement. Automatically connect to database.
$db->prepare($statement);

$db->execute($data);

// Get data from database.
$db->query($statement)->fetch();

// Disconnect from database.
unset($db);
```

## Model
Model will be initialized in application starting process

### Intention


### Usage
```php
// Initialize Model in app's init.
Model::init();

// Return Collection of data from database.
Model::where();
Model::first();
Model::all();
Model::find();

// Return Model::$database for creating custom SQL statement
Model::table();

// Create new Model
$newRecord = Model::new();
$newRecord->meta_key = 'name';
$newRecord->meta_value = 'Nawawish';

// Insert record to Model table
Model::add($newRecord);

Model::update();
Model::delete();
```
