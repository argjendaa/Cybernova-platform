<?php
/**
 * scan.php — CyberShield
 * Website Security Scanner me real-time AJAX
 */

include "config.php";

/* ── Auth guard ─────────────────────────────────────── */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = (int) $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

/* ── Scan history ───────────────────────────────────── */
$history = [];
$stmt = $conn->prepare("
    SELECT url, score, risk_level, scanned_at
    FROM   scans
    WHERE  user_id = ?
    ORDER  BY id DESC
    LIMIT  10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scan — CyberShield</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="scan.css">
</head>
<body>

<div class="overlay" id="overlay"></div>

<div class="topbar">
  <span class="topbar-logo">🛡 CyberNova</span>
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
    <li class="active"><a href="scan.php"><span class="nav-icon">🔍</span> Scan</a></li>
    <li><a href="alerts.php"><span class="nav-icon">🔔</span> Alerts</a></li>
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
      <h1>Security Scan</h1>
      <p>Skanoni sigurinë e website-it tuaj në kohë reale</p>
    </div>
  </div>

  <!-- ── Scan box ──────────────────────────────────── -->
  <div class="box scan-box">

    <h2>🔍 Start Scan</h2>

    <div id="error-box" class="scan-alert" style="display:none;"></div>

    <div class="scan-form">
      <input
        type="url"
        id="url-input"
        placeholder="https://example.com"
        autocomplete="off"
      >
      <button class="cs-btn" id="scan-btn" onclick="startScan()">
        ▶ Start Scan
      </button>
    </div>

    <!-- Progress -->
    <div id="scan-status" style="display:none; margin-top:20px;">
      <div class="prog-wrap">
        <div class="prog-bar" id="progress-bar"></div>
      </div>
      <div class="step-row">
        <span class="step" id="step-connect">Connecting</span>
        <span class="step" id="step-https">HTTPS</span>
        <span class="step" id="step-headers">Headers</span>
        <span class="step" id="step-analysis">Analysis</span>
        <span class="step" id="step-save">Saving</span>
      </div>
    </div>

    <!-- Result -->
    <div id="result-box" style="display:none; margin-top:22px; padding-top:22px; border-top:1px solid rgba(255,255,255,0.06);">
      <div class="result-row">

        <div class="score-ring">
          <svg width="84" height="84" viewBox="0 0 84 84">
            <circle class="ring-track" cx="42" cy="42" r="37"/>
            <circle class="ring-fill"  id="score-circle" cx="42" cy="42" r="37"/>
          </svg>
          <div class="score-num">
            <span id="score-val">—</span>
            <small>SCORE</small>
          </div>
        </div>

        <div class="result-info">
          <div id="result-url" class="result-url"></div>
          <div style="font-size:15px; font-weight:600; color:#f1f5f9; margin-bottom:8px;">Scan Complete</div>
          <span class="risk-badge" id="risk-badge"></span>
        </div>

      </div>

      <ul class="issues-list" id="issues-list"></ul>
    </div>

  </div>

  <!-- ── History ───────────────────────────────────── -->
  <div class="box" style="margin-top:16px; padding:0; overflow:hidden;">
    <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06);">
      <h2 style="margin:0;">📂 Scan History</h2>
    </div>
    <table class="cs-table">
      <thead>
        <tr>
          <th>URL</th>
          <th>Score</th>
          <th>Risk</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody id="history-body">
        <?php if (count($history) === 0): ?>
          <tr>
            <td colspan="4" style="text-align:center; padding:32px; color:#334155;">
              Nuk ka scans ende — filloni skanimin e parë!
            </td>
          </tr>
        <?php else:
          foreach ($history as $r):
            $rc = $r['risk_level'] === 'high' ? 'risk-high' : ($r['risk_level'] === 'medium' ? 'risk-medium' : 'risk-low');
        ?>
          <tr>
            <td class="cs-url" title="<?= htmlspecialchars($r['url']) ?>">
              <?= htmlspecialchars($r['url']) ?>
            </td>
            <td style="color:#e2e8f0; font-weight:600;"><?= $r['score'] ?>%</td>
            <td><span class="risk-badge <?= $rc ?>"><?= strtoupper($r['risk_level']) ?></span></td>
            <td><?= htmlspecialchars($r['scanned_at']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

</main>

<script>
/* ── Steps ─────────────────────────────────────────── */
function step(id, state) { document.getElementById(id).className = 'step ' + state; }
function prog(pct)        { document.getElementById('progress-bar').style.width = pct + '%'; }

/* ── Start scan ─────────────────────────────────────── */
async function startScan() {
  const url    = document.getElementById('url-input').value.trim();
  const btn    = document.getElementById('scan-btn');
  const errEl  = document.getElementById('error-box');

  errEl.style.display = 'none';

  if (!url) { showErr('Ju lutem vendosni një URL.'); return; }
  try { new URL(url); } catch { showErr('URL jo valide! Shembull: https://example.com'); return; }

  btn.disabled = true;
  btn.textContent = '⟳ Scanning…';
  document.getElementById('scan-status').style.display = 'block';
  document.getElementById('result-box').style.display  = 'none';

  ['step-connect','step-https','step-headers','step-analysis','step-save'].forEach(s => step(s, ''));
  prog(0);

  try {
    step('step-connect', 'active'); prog(10); await wait(400);

    const res = await fetch('scan_process.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'url=' + encodeURIComponent(url)
    });

    step('step-connect','done'); prog(25); step('step-https','active'); await wait(500);
    step('step-https','done');   prog(50); step('step-headers','active'); await wait(400);
    step('step-headers','done'); prog(70); step('step-analysis','active');

    const data = await res.json();

    await wait(400);
    step('step-analysis','done'); prog(90); step('step-save','active'); await wait(300);
    step('step-save','done'); prog(100);

    data.error ? showErr(data.error) : showResult(data, url);

  } catch { showErr('Gabim gjatë skanimit. Provoni përsëri.'); }

  btn.disabled = false;
  btn.textContent = '▶ Start Scan';
}

/* ── Show result ─────────────────────────────────────── */
function showResult(data, url) {
  document.getElementById('result-box').style.display = 'block';

  const circ = document.getElementById('score-circle');
  const r    = 37;
  const off  = (2 * Math.PI * r) * (1 - data.score / 100);
  circ.style.strokeDashoffset = off;
  circ.className = 'ring-fill' + (data.score < 60 ? ' danger' : data.score < 85 ? ' warn' : '');

  document.getElementById('score-val').textContent  = data.score + '%';
  document.getElementById('result-url').textContent = url;

  const b = document.getElementById('risk-badge');
  const m = { low:['risk-low','✔ Rrezik i Ulët'], medium:['risk-medium','⚠ Rrezik Mesatar'], high:['risk-high','✖ Rrezik i Lartë'] };
  b.className   = 'risk-badge ' + (m[data.risk]?.[0] || 'risk-low');
  b.textContent = m[data.risk]?.[1] || data.risk;

  const ul = document.getElementById('issues-list');
  ul.innerHTML = data.issues?.length
    ? data.issues.map(i => `<li class="issue-item"><span class="dot ${data.risk==='high'?'dot-danger':'dot-warn'}"></span>${esc(i)}</li>`).join('')
    : `<li class="issue-item"><span class="dot dot-ok"></span>✔ Asnjë problem i madh nuk u gjet</li>`;

  // Prepend to history
  const tbody = document.getElementById('history-body');
  const empty = tbody.querySelector('[colspan]');
  if (empty) empty.closest('tr').remove();
  const rc = {low:'risk-low',medium:'risk-medium',high:'risk-high'};
  const tr = document.createElement('tr');
  tr.innerHTML = `<td class="cs-url" title="${esc(url)}">${esc(url)}</td>
    <td style="color:#e2e8f0;font-weight:600;">${data.score}%</td>
    <td><span class="risk-badge ${rc[data.risk]||'risk-low'}">${data.risk.toUpperCase()}</span></td>
    <td>${new Date().toLocaleString('sq-AL',{dateStyle:'short',timeStyle:'short'})}</td>`;
  tbody.prepend(tr);
}

/* ── Error ──────────────────────────────────────────── */
function showErr(msg) {
  const el = document.getElementById('error-box');
  el.textContent = '⚠ ' + msg;
  el.style.display = 'block';
  document.getElementById('scan-status').style.display = 'none';
}

/* ── Helpers ────────────────────────────────────────── */
const wait = ms => new Promise(r => setTimeout(r, ms));
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

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