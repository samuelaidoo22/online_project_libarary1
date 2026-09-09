<?php
/**
 * GCTU Online Project Library System
 * Database Connection - Real MySQL Only
 */

$env = file_exists(__DIR__ . '/env.php') ? require __DIR__ . '/env.php' : [];
$host = $env['DB_HOST'] ?? '127.0.0.1';
$dbname = $env['DB_NAME'] ?? 'gctu_library';
$username = $env['DB_USER'] ?? 'root';
$password = $env['DB_PASS'] ?? '';

// Real MySQL connection only - no mock fallback
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
?>
