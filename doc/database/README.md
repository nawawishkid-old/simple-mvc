# Core\Database

> ต้องสร้าง `Core\App` เพื่อเป็น wrapper ให้ framework ด้วย จะได้ไม่ต้องใช้ static class มาก

## Query Builder
### Intentions
- Build any SQL statement string for later usage.
- Just return statement string

### Requirements
- Build any SQL statement with method-chaining technique intuitively.

### Usage
```php
$qb = new QueryBuilder();

// Store clauses and input values in the instance.
$qb->select('*')
    ->from('table_a')
    ->where('column_b', '=', 1220);

// Get statement
$select = $qb->get();

// Get prepared statement from stored clauses
$selectPrepared = $qb->getPrepared();

// Get stored input values
$selectValues = $qb->values();

// Reset instance, like instantiating a new one
$qb->clear();

$insert = $qb->insert(['column_a', 'column_b'])
             ->into('table_a')
             ->values([240, 730]);

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
// Connect to database.
$db = new DatabaseBridge();

// Prepare query statement.
$db->prepare($statement);

// Send query to database.
$db->send();

// Get data from database.
$db->fetch();

// Disconnect from database.
unset($db);
```

## Model
Model will be initialized in application starting process

### Intention


### Usage
```php
// Initialize Model in app's init
Model::init();

Model::where();
Model::first();
Model::all();
Model::find();
Model::add();
Model::create();
Model::update();
Model::delete();
```
