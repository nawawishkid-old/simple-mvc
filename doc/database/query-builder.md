# Core\Database\Query\Builder
> The key is making method-chaining rules.

## Usage
```php
use Core\Database\Query\Builder;

$qb = new Builder();

// Select data
$qb->table('tbl_a')
    ->select('col_a', 'col_b')
        ->where('col_id', '>', 20)
        ->andWhere('col_b', '<>', 'active')
        ->orWhere('col_c', 'any', function ($builder) {
            return $builder->table('tbl_b')
                        ->select('col_c')
                        ->where('col_c', 20);
        })
    ->innerJoin('tbl_b')
        ->on('tbl_a.col_a', '=', 'tbl_b.col_a')
        ->orOn('tbl_a.col_b', '=', 'tbl_b.col_b')
    ->groupBy('tbl_a.col_id')
        ->having('COUNT()', '>', 5)
    ->orderBy('COUNT(tbl_a.col_id)', 'desc');

$qb->table('tbl_a')
    ->select('*')
    ->whereExists( // Closure only?
        (new Builder)->table('tbl_b')
            ->select('col_a')
            ->where('col_b', '>', 20)
            ->get();
    );
    // whereNotExists

// Union
$qb->unionAll(function ($builer) {
    return $builder->table('tbl_a')->select('*')->get();
}, (new Builder)->table('tbl_b')->select('*')->get());

// Insertion
$qb->table('tbl_a')
    ->insert([
        'col_a' => 20,
        'col_b' => 300
    ]);

// Update
$qb->table('tbl_a')
    ->update([
        'col_a' => 50,
        'col_b' => 100
    ])
    ->where('col_a', '>', 50);

// Deletion
$qb->table('tbl_a')
    ->delete()
    ->where('col_id', '>', 1999);
```