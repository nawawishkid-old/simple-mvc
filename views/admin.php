<h1>Welcome, <?php echo $data->session['username']; ?>!</h1>
<form method="POST" action="/logout">
    <button>Logout</button>
</form>