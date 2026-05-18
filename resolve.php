<?php
include "config.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['id'])){
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("UPDATE alerts SET status='resolved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: alerts.php");
exit();