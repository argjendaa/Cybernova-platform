<?php

include "config.php";

if(isset($_POST['login'])){

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $res = $stmt->get_result();

    if($res->num_rows > 0){

        $user = $res->fetch_assoc();

        // ACCOUNT NOT VERIFIED
        if($user['is_verified'] == 0){

            $error = "Duhet ta verifikoni llogarinë fillimisht!";

        }

        // PASSWORD OK
        elseif(password_verify($password, $user['password'])){

            // SESSION DATA
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['email'];

            header("Location: dashboard.php");
            exit();

        }

        // WRONG PASSWORD
        else{

            $error = "Password i gabuar!";

        }

    }

    // EMAIL NOT FOUND
    else{

        $error = "Email nuk ekziston!";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

    <h2>Kyçu</h2>

    <?php if(isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">

        <input 
            type="email" 
            name="email" 
            placeholder="Email" 
            required
        >

        <input 
            type="password" 
            name="password" 
            placeholder="Password" 
            required
        >

        <button 
            type="submit" 
            name="login" 
            class="btn"
        >
            Kyçu
        </button>

    </form>

    <p>
        Nuk ke llogari?
        <a href="register.php">Regjistrohu</a>
    </p>

</div>

</body>
</html>