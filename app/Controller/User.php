<?php

namespace App\Controller;

use Core\Controller;
use App\Model\User as Model;
use Core\Database\Connection;
use Core\Database\Controller as DatabaseController;
use Core\Database\Query\Builder;
use Core\View\View;

class User extends Controller
{
    // ======================== Pages ========================
    public function loginPage($request, $response)
    {
        return View::get('login');
    }

    public function adminPage($request, $response)
    {
        session_name('id');
        session_start();
        
        return View::get('admin', [
            'session' => $_SESSION
        ]);
    }

    public function settingPage($request)
    {
        session_start();
        return View::get('user/setting', $request->user);
    }

    public function loginProcess($request, $response, $args)
    {
        $conn = new Connection();
        $ctrl = new DatabaseController($conn);

        $ctrl->table('wp_users')
                ->select('*')
                ->where('user_login', '=', $request->post['username']);

        $user = $ctrl->fetch()->first();
        $storedHashedPassword = '$2y$10$WkO26wE0rF6DOCk8x1r8D.qQmbof5QCN8L.wBf9BqsG5WroIT8wE2'; // $user->user_pass

        if (! $user || ! password_verify($request->post['password'], $storedHashedPassword)) {
            $args->authenticated = false;

            return View::get('login', $args);
        }
        
        session_name('id');
        session_start();

        $_SESSION['username'] = $user->user_login;
        
        return $response->redirect('admin');
    }

    public function logoutProcess($request, $response)
    {
        session_destroy();
        setcookie('id', '', time() - 3600);

        return $response->redirect('login');
    }

    public function user($request, $response, $arguments)
    {
        $conn = new Connection();
        $ctrl = new DatabaseController($conn);

        $ctrl->table('wp_users')
                ->select('*')
                ->where('user_login', '=', $arguments->username);

        // $result = (new View)->toJson($ctrl->fetch());
        $result = $ctrl->fetch()->toJson();

        return $response->data($result);
    }
}