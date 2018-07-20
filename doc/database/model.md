# Core\Database\Model

## Usage
```php
// Create new record
$model = new Model([
    'col_a' => 20,
    'col_b' => 30
])->save();

// Get records by returning an instance of DatabaseController to compose a query
Model::where('col_a', '=', 20)->get();

// Update
// Single
$model = Model::find(1);
$model->col_a = 100;
$model->save();

// Mass
Model::where('col_a', '>', 100)->update(['col_a' => 10]);
```