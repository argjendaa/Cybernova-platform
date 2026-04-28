<?php
include "config.php";

// 🔐 PROTECT PAGE
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

// -------------------------
// 📊 SAFE QUERIES (NO ERRORS)
// -------------------------

// TOTAL ALERTS
$res = $conn->query("SELECT COUNT(*) as total FROM alerts");
$alerts = $res ? $res->fetch_assoc()['total'] : 0;

// AVG SCORE
$res = $conn->query("SELECT AVG(score) as avg FROM scans");
$avg = $res && $res->fetch_assoc()['avg'] ? round($res->fetch_assoc()['avg']) : 0;

// TOTAL SCANS
$res = $conn->query("SELECT COUNT(*) as total FROM scans");
$scans = $res ? $res->fetch_assoc()['total'] : 0;

// MONTHLY GROWTH
$currentRes = $conn->query("
    SELECT AVG(score) as s FROM scans 
    WHERE MONTH(scanned_at)=MONTH(CURRENT_DATE())
");

$lastRes = $conn->query("
    SELECT AVG(score) as s FROM scans 
    WHERE MONTH(scanned_at)=MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
");

$current = $currentRes ? $currentRes->fetch_assoc()['s'] : 0;
$last = $lastRes ? $lastRes->fetch_assoc()['s'] : 0;

$growth = ($last && $current) ? round((($current - $last) / $last) * 100) : 0;

// -------------------------
// 📈 CHART DATA
// -------------------------

$data = $conn->query("
    SELECT DATE(scanned_at) as d, AVG(score) as s
    FROM scans
    GROUP BY DATE(scanned_at)
    ORDER BY d DESC LIMIT 7
");

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

// -------------------------
// ⚠ RECENT ALERTS
// -------------------------

$alertsList = [];
$res = $conn->query("SELECT message FROM alerts ORDER BY id DESC LIMIT 5");

if($res){
    while($row = $res->fetch_assoc()){
        $alertsList[] = $row['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logoBox">
        <h2>CyberNova</h2>
    </div>

    <ul>
        <li class="active"><a href="#">Dashboard</a></li>
        <li><a href="#">Scan</a></li>
        <li><a href="#">Alerts</a></li>
        <li><a href="#">Reports</a></li>
        <li><a href="#">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>

</div>

<!-- MAIN -->
<div class="main">

<!-- TOP -->
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

<!-- CHART -->
<div class="box">
    <h3>Security Trend</h3>
    <canvas id="chart"></canvas>
</div>

<!-- ALERTS -->
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
</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
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
            tension:0.4,
            fill:true
        }]
    },
    options:{
        plugins:{
            legend:{display:true}
        },
        scales:{
            y:{
                beginAtZero:true
            }
        }
    }
});
</script>

</body>
</html>