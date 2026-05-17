<?php
require_once "config.php";


?>

<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<title>Scan</title>


<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="links.css">

</head>

<body>

<div class="dashboard-container">

<!-- SIDEBAR -->
<div class="sidebar">
    

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

<div class="top">
    <h1>Website Security Scan</h1>
</div>

<!-- FORM -->
<div class="scan-box">
    <h2>Scan Website</h2>

    <form method="POST" action="scan_process.php">
        <input type="text" name="url" placeholder="https://example.com" required>
        <button class="btn" type="submit" name="scan">Start Scan</button>
    </form>
</div>

<!-- RESULT -->
<?php if(isset($_SESSION['result'])): ?>
<div class="result-box">

    <h2>Scan Result</h2>
    <div class="score"><?= $_SESSION['result']; ?>%</div>

    <ul>
        <?php if(!empty($_SESSION['issues'])): ?>
            <?php foreach($_SESSION['issues'] as $i): ?>
                <li>⚠ <?= $i ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li style="color:#22c55e;">✔ No major issues</li>
        <?php endif; ?>
    </ul>

</div>
<?php 
unset($_SESSION['result']);
unset($_SESSION['issues']);
endif; ?>

<!-- HISTORY -->
<div class="result-box">
    <h2>Scan History</h2>

    <table style="width:100%; color:white;">
        <tr>
            <th>Score</th>
            <th>Date</th>
        </tr>

        <?php
        $res = $conn->query("SELECT * FROM scans ORDER BY id DESC LIMIT 5");

        if($res && $res->num_rows > 0){
            while($row = $res->fetch_assoc()){
                echo "<tr>
                        <td>".$row['score']."%</td>
                        <td>".$row['scanned_at']."</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>Nuk ka scans ende</td></tr>";
        }
        ?>
    </table>
</div>

</div>
</div>

</body>
</html>