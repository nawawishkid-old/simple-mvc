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