<?php
include "config.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

// TOTAL ALERTS
$res = $conn->query("SELECT COUNT(*) as total FROM alerts");
$alerts = $res ? $res->fetch_assoc()['total'] : 0;

// AVG SCORE
$res = $conn->query("SELECT AVG(score) as avg FROM scans");
$row = $res ? $res->fetch_assoc() : null;
$avg = $row && $row['avg'] ? round($row['avg']) : 0;

// TOTAL SCANS
$res = $conn->query("SELECT COUNT(*) as total FROM scans");
$scans = $res ? $res->fetch_assoc()['total'] : 0;

// GROWTH
$currentRes = $conn->query("SELECT AVG(score) as s FROM scans WHERE MONTH(scanned_at)=MONTH(CURRENT_DATE())");
$lastRes = $conn->query("SELECT AVG(score) as s FROM scans WHERE MONTH(scanned_at)=MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)");

$current = $currentRes ? $currentRes->fetch_assoc()['s'] : 0;
$last = $lastRes ? $lastRes->fetch_assoc()['s'] : 0;

$growth = ($last > 0) ? round((($current - $last) / $last) * 100) : 0;

// CHART
$data = $conn->query("SELECT DATE(scanned_at) as d, AVG(score) as s FROM scans GROUP BY DATE(scanned_at) ORDER BY d DESC LIMIT 7");

$labels = [];
$values = [];

if($data){
    while($row = $data->fetch_assoc()){
        $labels[] = $row['d'];
        $values[] = round($row['s']);
    }
}

$labels = array_reverse($labels);
$values = array_reverse($values);

// ALERTS
$alertsList = [];
$res = $conn->query("SELECT message FROM alerts ORDER BY id DESC LIMIT 5");
if($res){
    while($row = $res->fetch_assoc()){
        $alertsList[] = $row['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CyberNova Dashboard</title>
<link rel="stylesheet" href="dashboard.css">

</head>

<body>

<!-- TOPBAR -->
<div class="menu-toggle" onclick="toggleMenu(this)">
    <span></span>
    <span></span>
    <span></span>
</div>
    


<!-- SIDEBAR (ONLY ONE) -->
<div class="sidebar" id="sidebar">

    <div class="logoBox">
        <h2>CyberNova</h2>
    </div>

    <ul>
        <li class="active"><a href="#">Dashboard</a></li>
        <li><a href="scan.php">Scan</a></li>
        <li><a href="alerts.php">Alerts</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="#">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>

</div>
<div class="overlay" onclick="toggleMenu()"></div>

<!-- MAIN -->
<div class="main">

    <div class="top">
        <div>
            <h1>Dashboard</h1>
            <small>Welcome, <?php echo $_SESSION['user']; ?></small>
        </div>
        <span class="date"><?php echo date("d M Y"); ?></span>
    </div>

    <!-- CARDS -->
    <div class="cards">

        <div class="card green">
            <h3>Security Score</h3>
            <p><?php echo $avg; ?>%</p>
            <small><?php echo $growth; ?>% this month</small>
        </div>

        <div class="card red">
            <h3>Active Alerts</h3>
            <p><?php echo $alerts; ?></p>
        </div>

        <div class="card blue">
            <h3>Total Scans</h3>
            <p><?php echo $scans; ?></p>
        </div>

        <div class="card purple">
            <h3>Status</h3>
            <p><?php echo ($avg > 80) ? "Secure" : "Risk"; ?></p>
        </div>

    </div>

    <!-- CONTENT -->
    <div class="content">

        <div class="box">
            <h3>Security Trend</h3>
            <canvas id="chart"></canvas>
        </div>

        <div class="box">
            <h3>Recent Alerts</h3>
            <ul class="alerts">
                <?php if(count($alertsList) > 0): ?>
                    <?php foreach($alertsList as $a): ?>
                        <li>⚠ <?php echo $a; ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No alerts found</li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function toggleMenu(){
    document.getElementById("sidebar").classList.toggle("active");
}

const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Security Score',
            data: <?php echo json_encode($values); ?>,
            borderColor: '#38bdf8',
            backgroundColor: 'rgba(56,189,248,0.1)',
            tension: 0.4,
            fill: true
        }]
    }
});
</script>
<canvas id="alertsChart"></canvas>
</body>
</html>