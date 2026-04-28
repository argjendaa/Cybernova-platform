<?php
include "config.php";

if(isset($_GET['token'])){

    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE token=?");
    $stmt->bind_param("s",$token);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){

        $stmt = $conn->prepare("UPDATE users SET is_verified=1, token=NULL WHERE token=?");
        $stmt->bind_param("s",$token);
        $stmt->execute();

        $success = "Llogaria u verifikua me sukses!";
    } else {
        $error = "Link invalid ose i përdorur!";
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<title>Verifiko</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<?php if(isset($success)): ?>
    <p class="success"><?= $success ?></p>
    <a href="login.php" class="btn">Vazhdo te Login</a>
<?php endif; ?>

<?php if(isset($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

</div>

</body>
</html>