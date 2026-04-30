

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Alerts - CyberNova</title>


<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="links.css">

</head>

<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        

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

        <div class="top">
            <h1>Security Alerts</h1>
            <span><?php echo date("d M Y"); ?></span>
        </div>

        <!-- ALERTS CONTENT -->
        <div class="alerts-page">

            <div class="alerts-container">

                <h2>Latest Alerts</h2>

                <div class="alerts-list">

                    <?php if($res && $res->num_rows > 0): ?>
                        
                        <?php while($row = $res->fetch_assoc()): ?>
                            
                            <div class="alerts-item">
                                <div class="alerts-text">
                                    ⚠ <?= htmlspecialchars($row['message']) ?>
                                </div>
                                <div class="alerts-date">
                                    <?= date("d M Y H:i", strtotime($row['created_at'])) ?>
                                </div>
                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <p class="alerts-empty">Nuk ka alerts për momentin</p>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>
</div>

</body>
</html>