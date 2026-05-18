<?php

require_once "config.php";

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");

    exit();

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $url = trim($_POST['url']);

    $issues = [];

    $score = 100;

    $risk = 'low';

    if (!filter_var($url, FILTER_VALIDATE_URL)) {

        $_SESSION['issues'] = ["URL jo valide"];

        $_SESSION['result'] = 0;

        header("Location: scan.php");

        exit();

    }

    $headers = @get_headers($url, 1);

    if (!$headers) {

        $issues[] = [
            "Website nuk përgjigjet",
            "high",
            "server"
        ];

        $score -= 50;

    }

    if (strpos($url, 'https://') !== 0) {

        $issues[] = [
            "Website nuk përdor HTTPS",
            "high",
            "ssl"
        ];

        $score -= 20;
         }

    if (!isset($headers['X-Frame-Options'])) {

        $issues[] = [
            "Missing X-Frame-Options",
            "medium",
            "headers"
        ];

        $score -= 10;

    }

    if (!isset($headers['Content-Security-Policy'])) {

        $issues[] = [
            "Missing CSP",
            "high",
            "headers"
        ];

        $score -= 10;

    }

    if ($score < 40) {

        $risk = 'high';

    }

    elseif ($score < 70) {

        $risk = 'medium';

    }

    if ($score < 0) {

        $score = 0;

    }

    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "
        INSERT INTO scans
        (user_id, url, score, risk_level)
        VALUES (?, ?, ?, ?)
         "
    );

    $stmt->bind_param(
        "isis",
        $userId,
        $url,
        $score,
        $risk
    );

    $stmt->execute();

    $scanId = $conn->insert_id;

    foreach ($issues as $issue) {

        $stmt = $conn->prepare(
            "
            INSERT INTO alerts
            (
                user_id,
                scan_id,
                message,
                severity,
                type,
                source_url
            )
            VALUES (?, ?, ?, ?, ?, ?)
            "
        );

        $stmt->bind_param(
            "iissss",
            $userId,
            $scanId,
            $issue[0],
            $issue[1],
            $issue[2],
            $url
        );

        $stmt->execute();

    }

    $_SESSION['result'] = $score;

    $_SESSION['issues'] =
        array_column($issues, 0);

    header("Location: scan.php");

    exit();
}