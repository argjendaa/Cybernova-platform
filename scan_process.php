<?php
/**
 * scan_process.php — CyberShield
 * AJAX endpoint: merr URL, bën scan, kthen JSON
 * Thirret nga scan.php me fetch()
 */

header('Content-Type: application/json');
include "config.php";

/* ── Protect ───────────────────────────────────────────────── */
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Jo i autentifikuar.']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/* ── Merr URL ───────────────────────────────────────────────── */
if (!isset($_POST['url'])) {
    echo json_encode(['error' => 'URL mungon.']);
    exit();
}

$url = trim($_POST['url']);

/* ── Validim URL ────────────────────────────────────────────── */
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'URL jo valide! Shembull: https://example.com']);
    exit();
}

/* ── Score & Issues ─────────────────────────────────────────── */
$score  = 100;
$issues = [];

/* ── 1. Website Check ───────────────────────────────────────── */
$context = stream_context_create([
    'http' => [
        'timeout'        => 8,
        'ignore_errors'  => true,
        'user_agent'     => 'CyberShield/1.0 SecurityScanner',
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ]
]);

$headers = @get_headers($url, 1, $context);

if (!$headers) {
    $issues[] = 'Website nuk përgjigjet ose nuk është i aksesueshëm';
    $score   -= 40;
}

/* ── 2. HTTPS Check ─────────────────────────────────────────── */
if (stripos($url, 'https://') !== 0) {
    $issues[] = 'Nuk përdor HTTPS — lidhja nuk është e enkriptuar';
    $score   -= 25;
}

/* ── 3. Security Headers ────────────────────────────────────── */
if ($headers) {
    $hFlat = array_change_key_case((array) $headers, CASE_LOWER);

    if (empty($hFlat['x-frame-options'])) {
        $issues[] = 'Mungon X-Frame-Options — rrezik Clickjacking';
        $score   -= 10;
    }
    if (empty($hFlat['x-content-type-options'])) {
        $issues[] = 'Mungon X-Content-Type-Options';
        $score   -= 5;
    }
    if (empty($hFlat['content-security-policy'])) {
        $issues[] = 'Mungon Content-Security-Policy (CSP)';
        $score   -= 10;
    }
    if (empty($hFlat['strict-transport-security'])) {
        $issues[] = 'Mungon HSTS (Strict-Transport-Security)';
        $score   -= 10;
    }
}

/* ── Limit Score ────────────────────────────────────────────── */
$score = max(0, min(100, $score));

/* ── Risk Level ─────────────────────────────────────────────── */
if      ($score >= 85) $risk = 'low';
elseif  ($score >= 60) $risk = 'medium';
else                   $risk = 'high';

/* ── Ruaj Scan ──────────────────────────────────────────────── */
$stmt = $conn->prepare("
    INSERT INTO scans (user_id, url, score, risk_level)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param('isis', $user_id, $url, $score, $risk);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Gabim gjatë ruajtjes në databazë.']);
    exit();
}
$stmt->close();

/* ── Ruaj Alerts ────────────────────────────────────────────── */
foreach ($issues as $issue) {
    $sev   = $risk;
    $stmt2 = $conn->prepare("
        INSERT INTO alerts (user_id, message, severity, status)
        VALUES (?, ?, ?, 'open')
    ");
    $stmt2->bind_param('iss', $user_id, $issue, $sev);
    $stmt2->execute();
    $stmt2->close();
}

/* ── Kthe rezultatin ────────────────────────────────────────── */
echo json_encode([
    'score'  => $score,
    'risk'   => $risk,
    'issues' => $issues,
]);