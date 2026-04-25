<?php
include "config.php";

if(isset($_POST['register'])){
    $_SESSION['user'] = $_POST['business']; // ruaj emrin
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<form method="POST">
    <h2>Register</h2>

    <input name="business" placeholder="Business" required>
    <input name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <button name="register">Register</button>
</form>

</body>
</html>