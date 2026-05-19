<?php
include "config.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   STATS
========================= */

$total_scans = 0;
$total_alerts = 0;
$safe_sites = 0;
$high_risk = 0;

/* TOTAL SCANS */
$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM scans
    WHERE user_id=?
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
$total_scans = $res->fetch_assoc()['total'] ?? 0;

/* TOTAL ALERTS */
$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM alerts
    WHERE user_id=?
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
$total_alerts = $res->fetch_assoc()['total'] ?? 0;

/* SAFE SITES */
$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM scans
    WHERE user_id=? AND risk_level='low'
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
$safe_sites = $res->fetch_assoc()['total'] ?? 0;

/* HIGH RISK */
$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM scans
    WHERE user_id=? AND risk_level='high'
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
$high_risk = $res->fetch_assoc()['total'] ?? 0;

/* CHART DATA */
$chart_labels = [];
$chart_scores = [];

$stmt = $conn->prepare("
    SELECT score, scanned_at
    FROM scans
    WHERE user_id=?
    ORDER BY id ASC
    LIMIT 7
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$res = $stmt->get_result();

while($row = $res->fetch_assoc()){

    $chart_labels[] = date("d M", strtotime($row['scanned_at']));
    $chart_scores[] = $row['score'];
}

/* RECENT ALERTS */
$stmt = $conn->prepare("
    SELECT message
    FROM alerts
    WHERE user_id=?
    ORDER BY id DESC
    LIMIT 5
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$alerts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<link rel="stylesheet" href="dashboard.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<!-- OVERLAY -->
<div class="overlay" id="overlay"></div>

<!-- MOBILE TOPBAR -->
<div class="topbar">

    <div class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
    </div>

</div>

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
        <li><a href="#">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>

</div>

<!-- MAIN -->
<div class="main">

    <h1>Cyber Security Dashboard</h1>

    <!-- CARDS -->
    <div class="cards">

        <div class="card green">
            <h3>Total Scans</h3>
            <h1><?= $total_scans ?></h1>
        </div>

        <div class="card red">
            <h3>Total Alerts</h3>
            <h1><?= $total_alerts ?></h1>
        </div>

        <div class="card blue">
            <h3>Safe Websites</h3>
            <h1><?= $safe_sites ?></h1>
        </div>

        <div class="card purple">
            <h3>High Risk</h3>
            <h1><?= $high_risk ?></h1>
        </div>

    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- CHART -->
        <div class="box">

            <h2>Security Analytics</h2>

            <canvas id="securityChart"></canvas>

        </div>

        <!-- ALERTS -->
        <div class="box">

            <h2>Recent Alerts</h2>

            <ul class="alerts">

                <?php
                if($alerts->num_rows > 0){

                    while($a = $alerts->fetch_assoc()){

                        echo "<li>⚠ ".htmlspecialchars($a['message'])."</li>";
                    }

                } else {

                    echo "<li>No alerts found</li>";
                }
                ?>

            </ul>

        </div>

    </div>

</div>

<!-- CHART -->
<script>

const ctx = document.getElementById('securityChart');

new Chart(ctx, {
    type: 'line',

    data: {

        labels: <?= json_encode($chart_labels) ?>,

        datasets: [{

            label: 'Security Score',

            data: <?= json_encode($chart_scores) ?>,

            borderColor: '#38bdf8',

            backgroundColor: 'rgba(56,189,248,0.15)',

            tension: 0.4,

            fill: true
        }]
    },

    options: {

        responsive: true,

        plugins: {
            legend: {
                labels: {
                    color: 'white'
                }
            }
        },

        scales: {

            y: {
                ticks: {
                    color: 'white'
                },

                grid: {
                    color: 'rgba(255,255,255,0.08)'
                }
            },

            x: {
                ticks: {
                    color: 'white'
                },

                grid: {
                    color: 'rgba(255,255,255,0.05)'
                }
            }
        }
    }
});

</script>

<!-- MOBILE MENU -->
<script>

const menuToggle = document.getElementById("menuToggle");
const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("overlay");

menuToggle.onclick = () => {

    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
    menuToggle.classList.toggle("active");
};

overlay.onclick = () => {

    sidebar.classList.remove("active");
    overlay.classList.remove("active");
    menuToggle.classList.remove("active");
};

</script>

</body>
</html>