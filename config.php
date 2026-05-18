<?php

declare(strict_types=1);

session_start();


$conn = new mysqli(
    "localhost",
    "root",
    "",
    "cybernova"
);

if ($conn->connect_error) {

    die("Database connection failed.");

}

/* SECURITY HEADERS */

header("X-Frame-Options: SAMEORIGIN");

header("X-Content-Type-Options: nosniff");

header("Referrer-Policy: no-referrer");

header("X-XSS-Protection: 1; mode=block");

/* CSRF TOKEN */

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}