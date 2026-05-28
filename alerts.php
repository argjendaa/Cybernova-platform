<?php
/**
 * alerts.php — CyberShield
 * Menaxhim i paralajmërimeve të sigurisë
 */

include "config.php";

/* ── Auth guard ─────────────────────────────────────── */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = (int) $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

/* ── Resolve alert ──────────────────────────────────── */
if (isset($_POST['resolve_id'])) {
    $id   = (int) $_POST['resolve_id'];
    $stmt = $conn->prepare("UPDATE alerts SET status='resolved' WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: alerts.php");
    exit();
}

/* ── Alerts list ────────────────────────────────────── */
$stmt = $conn->prepare("
    SELECT id, message, severity, status, created_at
    FROM   alerts
    WHERE  user_id = ?
    ORDER  BY id DESC
    LIMIT  30
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$alerts_res = $stmt->get_result();
$stmt->close();

/* ── Stats ──────────────────────────────────────────── */
function acount(mysqli $db, string $sev, int $uid): int {
    $s = $db->prepare("SELECT COUNT(*) n FROM alerts WHERE user_id=? AND severity=? AND status='open'");
    $s->bind_param("is", $uid, $sev);
    $s->execute();
    return (int) ($s->get_result()->fetch_assoc()['n'] ?? 0);
}

$cnt_high   = acount($conn, 'high',   $user_id);
$cnt_medium = acount($conn, 'medium', $user_id);
$cnt_low    = acount($conn, 'low',    $user_id);

$stmt = $conn->prepare("SELECT COUNT(*) n FROM alerts WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cnt_total = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alerts — CyberShield</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="alerts.css">
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
    <li class="active"><a href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a></li>
    <li><a href="reports.php"><span class="nav-icon">📋</span> Reports</a></li>
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
      <h1>Security Alerts</h1>
      <p>Monitor dhe menaxhoni kërcënimet në kohë reale</p>
    </div>
    <span style="font-size:13px; color:#334155;"><?= date("d M Y") ?></span>
  </div>

  <!-- Stat cards -->
  <div class="alerts-stats">
    <div class="stat-card high">
      <h3>High</h3>
      <p><?= $cnt_high ?></p>
    </div>
    <div class="stat-card medium">
      <h3>Medium</h3>
      <p><?= $cnt_medium ?></p>
    </div>
    <div class="stat-card low">
      <h3>Low</h3>
      <p><?= $cnt_low ?></p>
    </div>
    <div class="stat-card">
      <h3>Total</h3>
      <p><?= $cnt_total ?></p>
    </div>
  </div>

  <!-- List -->
  <div class="alerts-list">

    <?php if ($alerts_res && $alerts_res->num_rows > 0):
      while ($row = $alerts_res->fetch_assoc()): ?>

      <div class="alerts-card <?= $row['status'] === 'resolved' ? 'resolved' : '' ?>">

        <div class="alerts-left">
          <div class="alerts-badge <?= htmlspecialchars($row['severity']) ?>">
            <?= strtoupper($row['severity']) ?>
          </div>
          <div class="alerts-info">
            <h3><?= htmlspecialchars($row['message']) ?></h3>
            <p><?= date("d M Y · H:i", strtotime($row['created_at'])) ?></p>
          </div>
        </div>

        <div class="alerts-actions">
          <?php if ($row['status'] === 'open'): ?>
            <form method="POST">
              <input type="hidden" name="resolve_id" value="<?= (int) $row['id'] ?>">
              <button class="btn-resolve" type="submit">Resolve</button>
            </form>
          <?php else: ?>
            <span class="done">✔ Resolved</span>
          <?php endif; ?>
        </div>

      </div>

    <?php endwhile; else: ?>
      <div class="empty">Nuk ka alerts ende — kjo është lajm i mirë! 🎉</div>
    <?php endif; ?>

  </div>

</main>

<script>
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