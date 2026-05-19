<?php
include "config.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

/* =========================
   STATS
========================= */

// total scans
$totalScans = $conn->query("SELECT COUNT(*) t FROM scans")->fetch_assoc()['t'] ?? 0;

// avg score
$avgScore = $conn->query("SELECT AVG(score) s FROM scans")->fetch_assoc()['s'] ?? 0;
$avgScore = round($avgScore);

// alerts
$high = $conn->query("SELECT COUNT(*) t FROM alerts WHERE severity='high'")->fetch_assoc()['t'] ?? 0;
$medium = $conn->query("SELECT COUNT(*) t FROM alerts WHERE severity='medium'")->fetch_assoc()['t'] ?? 0;
$low = $conn->query("SELECT COUNT(*) t FROM alerts WHERE severity='low'")->fetch_assoc()['t'] ?? 0;

// recent scans
$scans = $conn->query("SELECT * FROM scans ORDER BY id DESC LIMIT 15");

/* =========================
   EXPORT CSV
========================= */
if(isset($_GET['export'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=report.csv');

    $output = fopen("php://output", "w");
    fputcsv($output, ['URL','Score','Risk','Date']);

    $res = $conn->query("SELECT url, score, risk_level, scanned_at FROM scans");

    while($row = $res->fetch_assoc()){
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
</head>
<body>

<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="reports.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <div class="sidebar-logo">
        <img src="library/logo(1).png">
    </div>

    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="scan.php">Scan</a></li>
        <li><a href="alerts.php">Alerts</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>

</div>
<!-- MAIN -->
<div class="main">

<div class="rp-page">

<!-- TOP -->
<div class="rp-top">
    <h1>Security Reports</h1>
    <a href="?export=1" class="rp-btn">Export CSV</a>
</div>

<!-- STATS -->
<div class="rp-stats">

    <div class="rp-card">
        <h3>Total Scans</h3>
        <p><?= $totalScans ?></p>
    </div>

    <div class="rp-card">
        <h3>Avg Score</h3>
        <p><?= $avgScore ?>%</p>
    </div>

    <div class="rp-card red">
        <h3>High Alerts</h3>
        <p><?= $high ?></p>
    </div>

    <div class="rp-card yellow">
        <h3>Medium Alerts</h3>
        <p><?= $medium ?></p>
    </div>

    <div class="rp-card green">
        <h3>Low Alerts</h3>
        <p><?= $low ?></p>
    </div>

</div>

<!-- CHART -->
<div class="rp-box">
    <h3>Security Score Trend</h3>
    <canvas id="chart"></canvas>
</div>

<!-- TABLE -->
<div class="rp-box">
    <h3>Recent Scans</h3>

    <table class="rp-table">
        <tr>
            <th>URL</th>
            <th>Score</th>
            <th>Risk</th>
            <th>Date</th>
        </tr>

        <?php while($row = $scans->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['url']) ?></td>
            <td><?= $row['score'] ?>%</td>
            <td class="rp-<?= $row['risk_level'] ?>">
                <?= strtoupper($row['risk_level']) ?>
            </td>
            <td><?= $row['scanned_at'] ?></td>
        </tr>
        <?php endwhile; ?>

    </table>
</div>

</div>

</div>
</div>

<script>
const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets: [{
            label: 'Score',
            data: [65,70,75,60,80,85,90],
            borderColor: '#38bdf8',
            tension:0.4
        }]
    }
});
</script>

</body>
</html>