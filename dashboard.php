<?php
/**
 * dashboard.php — CyberShield
 * Faqja kryesore: statistika, grafik, alerts të fundit
 */

include "config.php";

/* ── Auth guard ─────────────────────────────────────── */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = (int) $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

/* ── Stats ──────────────────────────────────────────── */
function dbcount(mysqli $db, string $sql, int $uid): int {
    $s = $db->prepare($sql);
    $s->bind_param("i", $uid);
    $s->execute();
    return (int) ($s->get_result()->fetch_assoc()['n'] ?? 0);
}

$total_scans  = dbcount($conn, "SELECT COUNT(*) n FROM scans  WHERE user_id=?", $user_id);
$total_alerts = dbcount($conn, "SELECT COUNT(*) n FROM alerts WHERE user_id=?", $user_id);
$safe_sites   = dbcount($conn, "SELECT COUNT(*) n FROM scans  WHERE user_id=? AND risk_level='low'",  $user_id);
$high_risk    = dbcount($conn, "SELECT COUNT(*) n FROM scans  WHERE user_id=? AND risk_level='high'", $user_id);

/* ── Chart data (last 7 scans) ──────────────────────── */
$chart_labels = [];
$chart_scores = [];

$stmt = $conn->prepare("
    SELECT score, scanned_at
    FROM   scans
    WHERE  user_id = ?
    ORDER  BY id ASC
    LIMIT  7
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $chart_labels[] = date("d M", strtotime($row['scanned_at']));
    $chart_scores[] = (int) $row['score'];
}
$stmt->close();

/* ── Recent alerts ──────────────────────────────────── */
$stmt = $conn->prepare("
    SELECT message, severity
    FROM   alerts
    WHERE  user_id = ?
    ORDER  BY id DESC
    LIMIT  5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_alerts = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — CyberShield</title>
  <link rel="stylesheet" href="dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- ══ OVERLAY ═══════════════════════════════════════ -->
<div class="overlay" id="overlay"></div>

<!-- ══ TOPBAR  (mobile) ══════════════════════════════ -->
<div class="topbar">
  <span class="topbar-logo">🛡 CyberShield</span>
  <div class="menu-toggle" id="menuToggle">
    <span></span><span></span><span></span>
  </div>
</div>

<!-- ══ SIDEBAR ═══════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

  <div class="sidebar-logo">
    <div class="logo-icon">🛡</div>
    <h1>CyberShield</h1>
  </div>

  <span class="nav-section">Menu</span>

  <ul>
    <li class="active">
      <a href="dashboard.php">
        <span class="nav-icon">📊</span> Dashboard
      </a>
    </li>
    <li>
      <a href="scan.php">
        <span class="nav-icon">🔍</span> Scan
      </a>
    </li>
    <li>
      <a href="alerts.php">
        <span class="nav-icon">🔔</span> Alerts
      </a>
    </li>
    <li>
      <a href="reports.php">
        <span class="nav-icon">📋</span> Reports
      </a>
    </li>
    <div class="sidebar-divider"></div>
    <li>
      <a href="settings.php">
        <span class="nav-icon">⚙</span> Settings
      </a>
    </li>
    <li>
      <a href="logout.php">
        <span class="nav-icon">🚪</span> Logout
      </a>
    </li>
  </ul>

  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($username) ?></div>
        <div class="user-role">Business Account</div>
      </div>
    </div>
  </div>

</aside>

<!-- ══ MAIN ══════════════════════════════════════════ -->
<main class="main">

  <div class="page-top">
    <div>
      <h1>Security Dashboard</h1>
      <p>Mirë se vini, <?= htmlspecialchars($username) ?> — ja gjendja e sigurisë tuaj sot.</p>
    </div>
    <a href="scan.php" class="cs-btn">🔍 Start Scan</a>
  </div>

  <!-- ── Stat cards ──────────────────────────────── -->
  <div class="cards">
    <div class="card green">
      <h3>Total Scans</h3>
      <h1><?= $total_scans ?></h1>
      <p>Skanime totale</p>
    </div>
    <div class="card red">
      <h3>Total Alerts</h3>
      <h1><?= $total_alerts ?></h1>
      <p>Paralajmërime</p>
    </div>
    <div class="card blue">
      <h3>Safe Sites</h3>
      <h1><?= $safe_sites ?></h1>
      <p>Rrezik i ulët</p>
    </div>
    <div class="card purple">
      <h3>High Risk</h3>
      <h1><?= $high_risk ?></h1>
      <p>Rrezik i lartë</p>
    </div>
  </div>

  <!-- ── Chart + Alerts ──────────────────────────── -->
  <div class="content">

    <div class="box">
      <h2>📈 Security Analytics</h2>
      <canvas id="securityChart" height="120"></canvas>
    </div>

    <div class="box">
      <h2>🔔 Recent Alerts</h2>
      <ul class="alerts">
        <?php if ($recent_alerts->num_rows > 0):
          while ($a = $recent_alerts->fetch_assoc()): ?>
          <li>⚠ <?= htmlspecialchars($a['message']) ?></li>
        <?php endwhile; else: ?>
          <li style="color:#334155;">Nuk ka alerts ende</li>
        <?php endif; ?>
      </ul>
      <a href="alerts.php" style="
        display:inline-block; margin-top:16px;
        font-size:12px; color:#38bdf8; text-decoration:none;
        ">Shiko të gjitha →</a>
    </div>

  </div>

</main>

<!-- ══ SCRIPTS ════════════════════════════════════════ -->
<script>
/* ── Chart ────────────────────────────────────────── */
new Chart(document.getElementById('securityChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($chart_labels) ?>,
    datasets: [{
      label: 'Security Score',
      data: <?= json_encode($chart_scores) ?>,
      borderColor: '#38bdf8',
      backgroundColor: 'rgba(56,189,248,0.08)',
      borderWidth: 2,
      tension: 0.42,
      fill: true,
      pointBackgroundColor: '#38bdf8',
      pointRadius: 4,
      pointHoverRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { labels: { color: '#64748b', font: { size: 12 } } }
    },
    scales: {
      y: {
        min: 0, max: 100,
        ticks: { color: '#475569', font: { size: 11 } },
        grid:  { color: 'rgba(255,255,255,0.04)' }
      },
      x: {
        ticks: { color: '#475569', font: { size: 11 } },
        grid:  { color: 'rgba(255,255,255,0.03)' }
      }
    }
  }
});

/* ── Mobile sidebar ───────────────────────────────── */
const toggle  = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

function openSidebar()  { sidebar.classList.add('active'); overlay.classList.add('active'); toggle.classList.add('active'); }
function closeSidebar() { sidebar.classList.remove('active'); overlay.classList.remove('active'); toggle.classList.remove('active'); }

toggle.addEventListener('click', () => sidebar.classList.contains('active') ? closeSidebar() : openSidebar());
overlay.addEventListener('click', closeSidebar);
</script>

</body>
</html>