<?php
/**
 * settings.php — CyberShield
 * Profili, fjalëkalimi, fshirja e historisë
 */

include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = (int) $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

/* ── Detect actual columns ──────────────────────────── */
$_cols = [];
$_cr = $conn->query("SHOW COLUMNS FROM users");
while ($_c = $_cr->fetch_assoc()) $_cols[] = $_c['Field'];

// Name column — provon variante të ndryshme
$_name_col = null;
foreach (['name','username','full_name','firstname','display_name'] as $_v) {
    if (in_array($_v, $_cols)) { $_name_col = $_v; break; }
}

// Date column
$_date_col = null;
foreach (['created_at','date_created','registered_at','reg_date','joined_at'] as $_v) {
    if (in_array($_v, $_cols)) { $_date_col = $_v; break; }
}

// Build SELECT
$_sel = ["email"];
if ($_name_col) $_sel[] = "$_name_col AS name";
if ($_date_col) $_sel[] = "$_date_col AS created_at";

/* ── Load user ──────────────────────────────────────── */
$stmt = $conn->prepare("SELECT " . implode(", ", $_sel) . " FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

// Fallback nëse nuk gjenden kolonat
if (empty($user['name']))       $user['name']       = $username;
if (empty($user['created_at'])) $user['created_at'] = date('Y-m-d H:i:s');

$success = '';
$error   = '';

/* ── Update profile ─────────────────────────────────── */
if (($_POST['action'] ?? '') === 'profile') {
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');

    if (strlen($name) < 2) {
        $error = 'Emri duhet të ketë të paktën 2 karaktere.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresa email nuk është valide.';
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $chk->bind_param("si", $email, $user_id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = 'Ky email përdoret nga një llogari tjetër.';
        } else {
            // Përdor kolonën e saktë të emrit
            if (!empty($_name_col)) {
                $upd = $conn->prepare("UPDATE users SET $_name_col=?, email=? WHERE id=?");
                $upd->bind_param("ssi", $name, $email, $user_id);
            } else {
                $upd = $conn->prepare("UPDATE users SET email=? WHERE id=?");
                $upd->bind_param("si", $email, $user_id);
            }
            $upd->execute();
            $upd->close();
            $_SESSION['username'] = $name;
            $username = $name;
            $user['name']  = $name;
            $user['email'] = $email;
            $success = 'Profili u përditësua me sukses.';
        }
        $chk->close();
    }
}

/* ── Change password ────────────────────────────────── */
if (($_POST['action'] ?? '') === 'password') {
    $cur  = $_POST['current_pw'] ?? '';
    $new  = $_POST['new_pw']     ?? '';
    $conf = $_POST['confirm_pw'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['password'] ?? '';
    $stmt->close();

    if (!password_verify($cur, $hash)) {
        $error = 'Fjalëkalimi aktual është i gabuar.';
    } elseif (strlen($new) < 8) {
        $error = 'Fjalëkalimi i ri duhet të ketë të paktën 8 karaktere.';
    } elseif ($new !== $conf) {
        $error = 'Fjalëkalimet e reja nuk përputhen.';
    } else {
        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $h   = password_hash($new, PASSWORD_BCRYPT);
        $upd->bind_param("si", $h, $user_id);
        $upd->execute();
        $upd->close();
        $success = 'Fjalëkalimi u ndryshua me sukses.';
    }
}

/* ── Clear history ──────────────────────────────────── */
if (($_POST['action'] ?? '') === 'clear_history') {
    $d1 = $conn->prepare("DELETE FROM scans  WHERE user_id=?"); $d1->bind_param("i",$user_id); $d1->execute(); $d1->close();
    $d2 = $conn->prepare("DELETE FROM alerts WHERE user_id=?"); $d2->bind_param("i",$user_id); $d2->execute(); $d2->close();
    $success = 'Historia dhe alertet u fshinë.';
}

/* ── Delete account ─────────────────────────────────── */
if (($_POST['action'] ?? '') === 'delete_account') {
    if (trim($_POST['confirm_delete'] ?? '') !== 'DELETE') {
        $error = 'Shkruani fjalën DELETE për të konfirmuar.';
    } else {
        foreach (['DELETE FROM alerts WHERE user_id=?','DELETE FROM scans WHERE user_id=?','DELETE FROM users WHERE id=?'] as $q) {
            $s = $conn->prepare($q); $s->bind_param("i",$user_id); $s->execute(); $s->close();
        }
        session_destroy();
        header("Location: register.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings — CyberShield</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="settings.css">
</head>
<body>

<div class="overlay" id="overlay"></div>

<div class="topbar">
  <img src="library/logo(2).png" alt="CyberNova" class="topbar-logo-img">
  <div class="menu-toggle" id="menuToggle"><span></span><span></span><span></span></div>
</div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="library/logo(2).png" alt="CyberNova" class="sidebar-logo-img">
  </div>
  <span class="nav-section">Menu</span>
  <ul>
    <li><a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
    <li><a href="scan.php"><span class="nav-icon">🔍</span> Scan</a></li>
    <li><a href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a></li>
    <li><a href="reports.php"><span class="nav-icon">📋</span> Reports</a></li>
    <div class="sidebar-divider"></div>
    <li class="active"><a href="settings.php"><span class="nav-icon">⚙</span> Settings</a></li>
    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($username,0,1)) ?></div>
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
      <h1>Settings</h1>
      <p>Menaxhoni llogarinë tuaj</p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="st-msg st-ok">✔ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="st-msg st-err">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="st-grid">

    <!-- ══ LEFT ═══════════════════════════════════════ -->
    <div class="st-col">

      <!-- Profile -->
      <div class="st-card">
        <div class="st-card-head">
          <span class="st-ico">👤</span>
          <div>
            <h2>Profili</h2>
            <p>Emri dhe email-i i llogarisë</p>
          </div>
        </div>

        <form method="POST" class="st-form">
          <input type="hidden" name="action" value="profile">

          <div class="st-field">
            <label>Emri i Plotë</label>
            <input type="text" name="name"
              value="<?= htmlspecialchars($user['name'] ?? '') ?>"
              placeholder="Emri juaj" required>
          </div>

          <div class="st-field">
            <label>Email</label>
            <input type="email" name="email"
              value="<?= htmlspecialchars($user['email'] ?? '') ?>"
              placeholder="email@kompania.com" required>
          </div>

          <div class="st-field">
            <label>Anëtar që nga</label>
            <div class="st-static">
              📅 <?= date("d M Y", strtotime($user['created_at'] ?? 'now')) ?>
            </div>
          </div>

          <button type="submit" class="cs-btn">💾 Ruaj</button>
        </form>
      </div>

      <!-- Password -->
      <div class="st-card">
        <div class="st-card-head">
          <span class="st-ico">🔒</span>
          <div>
            <h2>Fjalëkalimi</h2>
            <p>Ndrysho fjalëkalimin e llogarisë</p>
          </div>
        </div>

        <form method="POST" class="st-form">
          <input type="hidden" name="action" value="password">

          <div class="st-field">
            <label>Fjalëkalimi Aktual</label>
            <input type="password" name="current_pw" placeholder="••••••••" required>
          </div>

          <div class="st-field">
            <label>Fjalëkalimi i Ri</label>
            <input type="password" name="new_pw"
              placeholder="Min. 8 karaktere" required
              oninput="pwStr(this.value)">
            <div class="pw-track"><div class="pw-fill" id="pw-fill"></div></div>
          </div>

          <div class="st-field">
            <label>Konfirmo Fjalëkalimin</label>
            <input type="password" name="confirm_pw" placeholder="••••••••" required>
          </div>

          <button type="submit" class="cs-btn">🔑 Ndrysho</button>
        </form>
      </div>

    </div>

    <!-- ══ RIGHT ══════════════════════════════════════ -->
    <div class="st-col">

      <!-- Account info -->
      <div class="st-card">
        <div class="st-card-head">
          <span class="st-ico">🪪</span>
          <div>
            <h2>Të Dhënat e Llogarisë</h2>
            <p>Informacioni aktual i profilit</p>
          </div>
        </div>

        <div class="acc-info-list">
          <div class="acc-row">
            <span class="acc-label">Emri</span>
            <span class="acc-val"><?= htmlspecialchars($user['name'] ?? '—') ?></span>
          </div>
          <div class="acc-row">
            <span class="acc-label">Email</span>
            <span class="acc-val"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
          </div>
          <div class="acc-row">
            <span class="acc-label">Plani</span>
            <span class="acc-val"><span class="plan-pill">Basic · Free</span></span>
          </div>
          <div class="acc-row">
            <span class="acc-label">Regjistruar</span>
            <span class="acc-val"><?= date("d M Y", strtotime($user['created_at'] ?? 'now')) ?></span>
          </div>
          <div class="acc-row">
            <span class="acc-label">Statusi</span>
            <span class="acc-val"><span class="status-pill">✔ Aktiv</span></span>
          </div>
        </div>
      </div>

      <!-- Danger zone -->
      <div class="st-card st-danger-card">
        <div class="st-card-head">
          <span class="st-ico">⚠</span>
          <div>
            <h2>Zona e Rrezikut</h2>
            <p>Veprime të pakthyeshme</p>
          </div>
        </div>

        <!-- Clear history -->
        <form method="POST"
          onsubmit="return confirm('Jeni i sigurt? Skaниmet dhe alertet do të fshihen.')">
          <input type="hidden" name="action" value="clear_history">
          <div class="danger-row">
            <div class="danger-info">
              <strong>Fshi Historinë</strong>
              <small>Të gjitha skaниmet dhe alertet</small>
            </div>
            <button type="submit" class="btn-del">🗑 Fshi</button>
          </div>
        </form>

        <div class="danger-sep"></div>

        <!-- Delete account -->
        <div class="danger-row">
          <div class="danger-info">
            <strong>Fshi Llogarinë</strong>
            <small>Të gjitha të dhënat humbasin përgjithmonë</small>
          </div>
          <button type="button" class="btn-del btn-del-hard"
            onclick="toggleDel()">🚨 Fshi</button>
        </div>

        <form method="POST" class="del-confirm" id="del-confirm">
          <input type="hidden" name="action" value="delete_account">
          <p>Shkruani <strong>DELETE</strong> për të konfirmuar:</p>
          <div class="del-row">
            <input type="text" name="confirm_delete" placeholder="DELETE" autocomplete="off">
            <button type="submit" class="btn-del btn-del-hard">Konfirmo</button>
          </div>
        </form>

      </div>

    </div>

  </div><!-- /st-grid -->

</main>

<script>
function pwStr(pw) {
  let s = 0;
  if (pw.length >= 8)           s++;
  if (/[A-Z]/.test(pw))         s++;
  if (/[0-9]/.test(pw))         s++;
  if (/[^A-Za-z0-9]/.test(pw))  s++;
  const f = document.getElementById('pw-fill');
  f.style.width      = (s * 25) + '%';
  f.style.background = ['','#ef4444','#f59e0b','#38bdf8','#22c55e'][s];
}

function toggleDel() {
  document.getElementById('del-confirm').classList.toggle('show');
}

const toggle  = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
function openSB()  { sidebar.classList.add('active');    overlay.classList.add('active');    toggle.classList.add('active');    }
function closeSB() { sidebar.classList.remove('active'); overlay.classList.remove('active'); toggle.classList.remove('active'); }
toggle.addEventListener('click',  () => sidebar.classList.contains('active') ? closeSB() : openSB());
overlay.addEventListener('click', closeSB);
</script>

</body>
</html>