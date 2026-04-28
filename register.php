<?php
include "config.php";

if(isset($_POST['register'])){

    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $business = trim($_POST['business']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password_raw = $_POST['password'];

    if(empty($first) || empty($last) || empty($business) || empty($email) || empty($phone) || empty($password_raw)){
        $error = "Plotësoni të gjitha fushat!";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows > 0){
            $error = "Email ekziston!";
        } else {

            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            $stmt = $conn->prepare("INSERT INTO users 
            (first_name,last_name,business_name,email,phone,password,token,is_verified)
            VALUES (?,?,?,?,?,?,?,0)");

            $stmt->bind_param(
                "sssssss",
                $first,
                $last,
                $business,
                $email,
                $phone,
                $password,
                $token
            );

            $stmt->execute();

            header("Location: verify.php?token=".$token);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<title>Register</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Regjistrohu</h2>

    <?php if(isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input name="first_name" placeholder="Emri" required>
        <input name="last_name" placeholder="Mbiemri" required>
        <input name="business" placeholder="Business Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input name="phone" placeholder="Nr Telefonit" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="register" class="btn">Regjistrohu</button>
    </form>

    <p>Ke llogari? <a href="login.php">Kyçu</a></p>
</div>

</body>
</html>