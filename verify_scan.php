<?php
/**
 * verify.php — CyberShield
 * Verifikon email-in e userit me token
 */

include "config.php";

$status = 'invalid'; // default

$token = trim($_GET['token'] ?? '');

if ($token) {

    /* ── Kërko userin me këtë token ── */
    $stmt = $conn->prepare("
        SELECT id, name, email_verified
        FROM   users
        WHERE  email_token = ?
        LIMIT  1
    ");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {

        if ($row['email_verified']) {

            // Tashmë i verifikuar
            $status   = 'already';
            $userName = $row['name'];

        } else {

            // Verifiko
            $upd = $conn->prepare("
                UPDATE users
                SET    email_verified = 1,
                       email_token    = NULL
                WHERE  id = ?
            ");
            $upd->bind_param('i', $row['id']);
            $upd->execute();
            $upd->close();

            $status   = 'ok';
            $userName = $row['name'];
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konfirmim Email — CyberShield</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg:      #050a10;
  --surface: #0c1421;
  --border:  #1a2d45;
  --accent:  #00f0ff;
  --accent2: #0af5a0;
  --danger:  #ff3c5e;
  --muted:   #4a6a85;
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body {
  background: var(--bg);
  font-family: 'Syne', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
body::before {
  content:'';
  position:fixed; inset:0;
  background-image:
    linear-gradient(rgba(0,240,255,.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,240,255,.03) 1px,transparent 1px);
  background-size:40px 40px;
  pointer-events:none;
}
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 52px 44px;
  max-width: 420px;
  width: 100%;
  text-align: center;
  box-shadow: 0 0 40px rgba(0,240,255,.08);
}
.icon { font-size: 3.5rem; margin-bottom: 16px; display: block; }
h1 {
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #fff;
  margin-bottom: 10px;
}
p {
  color: var(--muted);
  font-size: .88rem;
  line-height: 1.6;
  margin-bottom: 28px;
}
.btn {
  display: inline-block;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  color: #000;
  text-decoration: none;
  border-radius: 8px;
  padding: 13px 30px;
  font-weight: 800;
  font-size: .9rem;
  transition: opacity .2s;
}
.btn:hover { opacity: .85; }
.logo {
  display: flex; align-items: center;
  justify-content: center; gap: 8px;
  margin-bottom: 32px;
}
.logo-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  border-radius: 8px; display: grid; place-items: center; font-size: 17px;
}
.logo-text { font-size: 1.1rem; font-weight: 800; color: #fff; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">🛡</div>
    <span class="logo-text">CyberShield</span>
  </div>

  <?php if ($status === 'ok'): ?>
    <span class="icon">✅</span>
    <h1>Email Konfirmuar!</h1>
    <p>
      Mirë se vini, <strong style="color:#fff"><?= htmlspecialchars($userName) ?></strong>!<br>
      Llogaria juaj është aktivizuar. Mund të kyçeni tani.
    </p>
    <a href="login.php" class="btn">🚀 Kyçuni Tani</a>

  <?php elseif ($status === 'already'): ?>
    <span class="icon">ℹ️</span>
    <h1>Tashmë i Verifikuar</h1>
    <p>
      Llogaria e <strong style="color:#fff"><?= htmlspecialchars($userName) ?></strong>
      është aktivizuar tashmë.
    </p>
    <a href="login.php" class="btn">→ Kyçuni</a>

  <?php else: ?>
    <span class="icon">❌</span>
    <h1>Link Jo Valid</h1>
    <p>
      Ky link konfirmimi ka skaduar ose nuk është i saktë.<br>
      Regjistrohuni përsëri ose kontaktoni mbështetjen.
    </p>
    <a href="register.php" class="btn">← Regjistrohu</a>

  <?php endif; ?>
</div>
</body>
</html>