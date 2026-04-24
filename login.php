<?php
include "config.php";

if(isset($_POST['login'])){
    $_SESSION['user'] = $_POST['email']; // ruan diçka për display
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>

<form method="POST">
    <h2>Kyqu</h2>

    <input name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <button name="login">Kyqu</button>
</form>

</body>
</html>