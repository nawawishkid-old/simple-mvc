# Simple MVC

## Disclaimer!
Bad English below!

This is just a demonstration of how, according to my understanding, the web backend application flows. Not a fully functioning web development framework! Furthermore, I may or may not develop this repo in the future. Because I've done this framework while I have little knowledge of the web development world, there may be a lot of bad practice, or something like that, I don't know. Any suggestion is welcome :)

## List of "don't have it yet"
- Model class. You have to connect to database in Controller :P.
- Database migration. Yeah, create your database schema before touching this framework.
- Security protection. I haven't done any security protection like XSS or CSRF. So, take care of yourself :D.
- Template engine. Just plain .php or .html, bro!

## Usages
Suppose you've already had a user database table, let's see an example of basic login system... yeah, login, no registration here :P

#### Controller
In `app/Controller` directory
```php

namespace App\Controller;

use Core\Controller;
use Core\View\View;
use Core\Database\Connection;
use Core\Database\Controller as DatabaseController;

class User extends Controller
{
    public function index($req, $res)
    {
        $conn = new Connection();
        $ctrl = new DatabaseController($conn);

        $ctrl->table('tbl_a')
                ->select('*');

        $rows = $ctrl->fetch();

        return View::get('user/index', $rows);
    }

    public function loginPage($req, $res)
    {
        return View::get('user/login');
    }

    public function adminPage($req, $res)
    {
        return View::get('user/admin', $data);
    }

    public function loginProcess($req, $res)
    {
        // ...
        // some authentication process here
        // ...

        return $res->redirect('user/admin');
    }

    public function logoutProcess($req, $res)
    {
        // ...
        // logout process like destroy session and its cookie
        // ...

        return $response->redirect('user/login');
    }
}

```

#### URI Routing
In `routes` directory
```php

use Core\User\API\Route;
use App\Controller as Ctrl;

Route::get('users', [Ctrl\User::class, 'index']);
Route::get('login', [Ctrl\User::class, 'loginPage']);
Route::post('login', [Ctrl\User::class, 'loginProcess']);
Route::post('logout', [Ctrl\User::class, 'logoutProcess']);
Route::get('admin', [Ctrl\User::class, 'adminPage'])
    ->middleware(function ($req, $res, $args) {
        if (empty($_COOKIE['id'])) {
            $response->redirect('login');
        }

        return true;
    });

```

#### View
In `views` directory
**File structure**  
```
- views
    - user
        - admin.php
        - index.php
        - login.php
```

First, index.php
```php

// index.php
<h1>All users</h1>
<table>
    <thead>
        <tr>
            <td>User ID</td>
            <td>Username</td>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($data->all() as $user) {
            echo '<tr><td>' . $user->id . '</td><td>' . $user->name . '</td></tr>';
        }
    ?>
    </tbody>
</table>

```

then, login.php
```php

// login.php
<h1>Login</h1>
<?php
    if ($data->authenticated === false) {
        echo '<b>Incorrect username or password</b>';
    }
?>
<form method="POST" action="/login">
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button>Login</button>
</form>

```

and... admin.php
```php

// admin.php
<h1>Welcome, <?php echo $data->session['username']; ?>!</h1>
<form method="POST" action="/logout">
    <button>Logout</button>
</form>

```