<?php
include "config.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

/* RESOLVE */
if(isset($_POST['resolve_id'])){
    $id = intval($_POST['resolve_id']);
    $stmt = $conn->prepare("UPDATE alerts SET status='resolved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: alerts.php");
    exit();
}

/* GET ALERTS */
$stmt = $conn->prepare("
SELECT id, message, severity, status, created_at 
FROM alerts 
ORDER BY id DESC LIMIT 20
");
$stmt->execute();
$res = $stmt->get_result();

/* STATS */
$high = $conn->query("SELECT COUNT(*) t FROM alerts WHERE severity='high' AND status='open'")->fetch_assoc()['t'] ?? 0;
$medium = $conn->query("SELECT COUNT(*) t FROM alerts WHERE severity='medium' AND status='open'")->fetch_assoc()['t'] ?? 0;
$low = $conn->query("SELECT COUNT(*) t FROM alerts WHERE severity='low' AND status='open'")->fetch_assoc()['t'] ?? 0;
$total = $conn->query("SELECT COUNT(*) t FROM alerts")->fetch_assoc()['t'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Alerts</title>

<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="alerts.css">
<script src="script.js"></script>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>CyberNova</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="scan.php">Scan</a></li>
        <li class="active"><a href="#">Alerts</a></li>
        <li><a href="#">Reports</a></li>
        <li><a href="#">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<!-- MAIN -->
<div class="main">

<div class="alerts-page">

<!-- HEADER -->
<div class="alerts-header">
    <div>
        <h1>Security Alerts</h1>
        <p>Monitor threats in real-time</p>
    </div>
    <span><?= date("d M Y") ?></span>
</div>

<!-- STATS -->
<div class="alerts-stats">

    <div class="stat-card high">
        <h3>High</h3>
        <p><?= $high ?></p>
    </div>

    <div class="stat-card medium">
        <h3>Medium</h3>
        <p><?= $medium ?></p>
    </div>

    <div class="stat-card low">
        <h3>Low</h3>
        <p><?= $low ?></p>
    </div>

    <div class="stat-card">
        <h3>Total</h3>
        <p><?= $total ?></p>
    </div>

</div>

<!-- LIST -->
<div class="alerts-list">

<?php if($res && $res->num_rows > 0): ?>

<?php while($row = $res->fetch_assoc()): ?>

<div class="alerts-card <?= $row['status']=='resolved' ? 'resolved' : '' ?>">

    <div class="alerts-left">

        <div class="alerts-badge <?= $row['severity'] ?>">
            <?= strtoupper($row['severity']) ?>
        </div>

        <div class="alerts-info">
            <h3><?= htmlspecialchars($row['message']) ?></h3>
            <p><?= date("d M Y H:i", strtotime($row['created_at'])) ?></p>
        </div>

    </div>

    <div class="alerts-actions">

        <?php if($row['status']=='open'): ?>
        <form method="POST">
            <input type="hidden" name="resolve_id" value="<?= $row['id'] ?>">
            <button class="btn-resolve">Resolve</button>
        </form>
        <?php else: ?>
            <span class="done">✔ Resolved</span>
        <?php endif; ?>

    </div>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="empty">
    Nuk ka alerts
</div>

<?php endif; ?>

</div>

</div>
</div>
</div>

</body>
</html>