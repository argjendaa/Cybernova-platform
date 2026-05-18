<?php

include "config.php";

if(isset($_POST['login'])){
   

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){

        $user = $res->fetch_assoc();

        if($user['is_verified'] == 0){
            $error = "Duhet ta verifikoni llogarinë fillimisht!";
        }
        elseif(password_verify($password, $user['password'])){
            $_SESSION['user'] = $user['email'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password i gabuar!";
        }

    } else {
        $error = "Email nuk ekziston!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>

<div class="container">
    <h2>Kyçu</h2>

    <?php if(isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" class="btn">Kyçu</button>
    </form>

   
    <p>Nuk ke llogari? <a href="register.php">Regjistrohu</a></p>
</div>

</body>
</html>