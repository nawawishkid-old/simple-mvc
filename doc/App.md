# Core\App

## Requirements
- Setup the components bundle.

## Usage
```php

use Core\Http\Request;
use Core\Router\Router;

$app = new App([
    'request' => Request::class, 
    'router' => Router::class
]);

```