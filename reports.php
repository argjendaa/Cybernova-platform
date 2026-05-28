<?php
/**
 * reports.php — CyberShield
 * Raportet e sigurisë me eksport CSV
 */

include "config.php";

/* ── Auth guard ─────────────────────────────────────── */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = (int) $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

/* ── Export CSV ─────────────────────────────────────── */
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="cybershield-report-' . date('Y-m-d') . '.csv"');

    $out = fopen("php://output", "w");
    fputcsv($out, ['URL', 'Score', 'Risk Level', 'Date']);

    $stmt = $conn->prepare("SELECT url, score, risk_level, scanned_at FROM scans WHERE user_id=? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, $row);
    }

    fclose($out);
    exit();
}

/* ── Stats ──────────────────────────────────────────── */
$stmt = $conn->prepare("SELECT COUNT(*) n FROM scans WHERE user_id=?");
$stmt->bind_param("i", $user_id); $stmt->execute();
$totalScans = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);

$stmt = $conn->prepare("SELECT COALESCE(AVG(score),0) n FROM scans WHERE user_id=?");
$stmt->bind_param("i", $user_id); $stmt->execute();
$avgScore = round((float) ($stmt->get_result()->fetch_assoc()['n'] ?? 0));

function sev(mysqli $db, string $s, int $uid): int {
    $q = $db->prepare("SELECT COUNT(*) n FROM alerts WHERE user_id=? AND severity=?");
    $q->bind_param("is", $uid, $s);
    $q->execute();
    return (int) ($q->get_result()->fetch_assoc()['n'] ?? 0);
}

$cnt_high   = sev($conn, 'high',   $user_id);
$cnt_medium = sev($conn, 'medium', $user_id);
$cnt_low    = sev($conn, 'low',    $user_id);

/* ── Chart data ─────────────────────────────────────── */
$chart_labels = [];
$chart_scores = [];

$stmt = $conn->prepare("SELECT score, scanned_at FROM scans WHERE user_id=? ORDER BY id ASC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $chart_labels[] = date("d M", strtotime($row['scanned_at']));
    $chart_scores[] = (int) $row['score'];
}
$stmt->close();

/* ── Recent scans table ─────────────────────────────── */
$stmt = $conn->prepare("SELECT url, score, risk_level, scanned_at FROM scans WHERE user_id=? ORDER BY id DESC LIMIT 15");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$scans = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports — CyberShield</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="reports.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="overlay" id="overlay"></div>

<div class="topbar">
  <span class="topbar-logo">🛡 CyberShield</span>
  <div class="menu-toggle" id="menuToggle">
    <span></span><span></span><span></span>
  </div>
</div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">🛡</div>
    <h1>CyberShield</h1>
  </div>
  <span class="nav-section">Menu</span>
  <ul>
    <li><a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
    <li><a href="scan.php"><span class="nav-icon">🔍</span> Scan</a></li>
    <li><a href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a></li>
    <li class="active"><a href="reports.php"><span class="nav-icon">📋</span> Reports</a></li>
    <div class="sidebar-divider"></div>
    <li><a href="settings.php"><span class="nav-icon">⚙</span> Settings</a></li>
    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
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

<main class="main">

  <div class="page-top">
    <div>
      <h1>Security Reports</h1>
      <p>Analizë e plotë e gjendjes së sigurisë</p>
    </div>
    <a href="?export=1" class="cs-btn">⬇ Export CSV</a>
  </div>

  <!-- Stats -->
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
      <p><?= $cnt_high ?></p>
    </div>
    <div class="rp-card yellow">
      <h3>Medium Alerts</h3>
      <p><?= $cnt_medium ?></p>
    </div>
    <div class="rp-card green">
      <h3>Low Alerts</h3>
      <p><?= $cnt_low ?></p>
    </div>
  </div>

  <!-- Chart -->
  <div class="rp-box">
    <h3>📈 Security Score Trend</h3>
    <div class="rp-chart-wrap">
      <canvas id="rpChart"></canvas>
    </div>
  </div>

  <!-- Table -->
  <div class="rp-box">
    <h3>🗂 Recent Scans</h3>
    <table class="rp-table">
      <thead>
        <tr>
          <th>URL</th>
          <th>Score</th>
          <th>Risk</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($scans && $scans->num_rows > 0):
          while ($row = $scans->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['url']) ?></td>
            <td><strong style="color:#e2e8f0;"><?= $row['score'] ?>%</strong></td>
            <td class="rp-<?= htmlspecialchars($row['risk_level']) ?>"><?= strtoupper($row['risk_level']) ?></td>
            <td><?= htmlspecialchars($row['scanned_at']) ?></td>
          </tr>
        <?php endwhile; else: ?>
          <tr>
            <td colspan="4" style="text-align:center; color:#334155; padding:32px;">
              Nuk ka scans ende
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>

<script>
/* ── Chart ─────────────────────────────────────────── */
new Chart(document.getElementById('rpChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($chart_labels) ?>,
    datasets: [{
      label: 'Security Score',
      data: <?= json_encode($chart_scores) ?>,
      borderColor: '#38bdf8',
      backgroundColor: 'rgba(56,189,248,0.07)',
      borderWidth: 2,
      tension: 0.42,
      fill: true,
      pointBackgroundColor: '#38bdf8',
      pointRadius: 4,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { color: '#64748b', font: { size: 12 } } } },
    scales: {
      y: {
        min: 0, max: 100,
        ticks: { color: '#475569' },
        grid:  { color: 'rgba(255,255,255,0.04)' }
      },
      x: {
        ticks: { color: '#475569' },
        grid:  { color: 'rgba(255,255,255,0.03)' }
      }
    }
  }
});

/* ── Mobile sidebar ─────────────────────────────────── */
const toggle  = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
function open()  { sidebar.classList.add('active'); overlay.classList.add('active'); toggle.classList.add('active'); }
function close() { sidebar.classList.remove('active'); overlay.classList.remove('active'); toggle.classList.remove('active'); }
toggle.addEventListener('click', () => sidebar.classList.contains('active') ? close() : open());
overlay.addEventListener('click', close);
</script>

</body>
</html>