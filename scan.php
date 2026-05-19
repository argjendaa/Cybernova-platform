<?php
include "config.php";

/* ==========================
   PROTECT PAGE
========================== */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

/* ==========================
   USER
========================== */
$user_id = $_SESSION['user_id'];

$result = null;
$issues = [];
$error = "";

/* ==========================
   SCAN LOGIC
========================== */
if(isset($_POST['scan'])){

    $url = trim($_POST['url']);

    /* URL VALIDATION */
    if(!filter_var($url, FILTER_VALIDATE_URL)){

        $error = "URL jo valide!";

    } else {

        $score = 100;

        /* ==========================
           WEBSITE CHECK
        ========================== */
        $headers = @get_headers($url);

        if(!$headers){

            $issues[] = "Website nuk përgjigjet";
            $score -= 50;

        }

        /* HTTPS CHECK */
        if(strpos($url, "https://") !== 0){

            $issues[] = "Nuk përdor HTTPS";
            $score -= 20;

        }

        /* SECURITY HEADERS */
        if(!$headers || count($headers) < 5){

            $issues[] = "Security headers të dobëta";
            $score -= 10;

        }

        /* LIMIT SCORE */
        if($score < 0){
            $score = 0;
        }

        /* ==========================
           RISK LEVEL
        ========================== */
        if($score >= 85){

            $risk = "low";

        }
        elseif($score >= 60){

            $risk = "medium";

        }
        else{

            $risk = "high";

        }

        /* ==========================
           SAVE SCAN
        ========================== */
        $stmt = $conn->prepare("
            INSERT INTO scans
            (user_id, url, score, risk_level)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isis",
            $user_id,
            $url,
            $score,
            $risk
        );

        if(!$stmt->execute()){

            $error = "Gabim gjatë ruajtjes së scan.";

        } else {

            /* ==========================
               SAVE ALERTS
            ========================== */
            foreach($issues as $issue){

                $stmt2 = $conn->prepare("
                    INSERT INTO alerts
                    (user_id, message, severity, status)
                    VALUES (?, ?, ?, 'open')
                ");

                $stmt2->bind_param(
                    "iss",
                    $user_id,
                    $issue,
                    $risk
                );

                $stmt2->execute();
            }

            $result = $score;
        }
    }
}
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

<div class="top">
    <h1>Website Security Scan</h1>
</div>

<!-- SCAN FORM -->
<div class="scan-box">

    <h2>Scan Website</h2>

    <?php if($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="url"
            placeholder="https://example.com"
            required
        >

        <button class="btn" name="scan">
            Start Scan
        </button>

    </form>

</div>

<!-- RESULT -->
<?php if($result !== null): ?>

<div class="result-box">

    <h2>Scan Result</h2>

    <div class="score">
        <?= $result ?>%
    </div>

    <ul>

        <?php if(count($issues) > 0): ?>

            <?php foreach($issues as $i): ?>

                <li>
                    ⚠ <?= htmlspecialchars($i) ?>
                </li>

            <?php endforeach; ?>

        <?php else: ?>

            <li class="success">
                ✔ No major issues found
            </li>

        <?php endif; ?>

    </ul>

</div>

<?php endif; ?>

<!-- HISTORY -->
<div class="result-box">

    <h2>Scan History</h2>

    <table style="width:100%; color:white;">

        <tr>
            <th>URL</th>
            <th>Score</th>
            <th>Risk</th>
            <th>Date</th>
        </tr>

        <?php

        $stmt = $conn->prepare("
            SELECT *
            FROM scans
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 5
        ");

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $res = $stmt->get_result();

        if($res && $res->num_rows > 0){

            while($row = $res->fetch_assoc()){

                echo "
                <tr>
                    <td>".htmlspecialchars($row['url'])."</td>
                    <td>".$row['score']."%</td>
                    <td>".strtoupper($row['risk_level'])."</td>
                    <td>".$row['scanned_at']."</td>
                </tr>
                ";
            }

        } else {

            echo "
            <tr>
                <td colspan='4'>
                    Nuk ka scans ende
                </td>
            </tr>
            ";
        }

        ?>

    </table>

</div>

</div>
</div>

</body>
</html>