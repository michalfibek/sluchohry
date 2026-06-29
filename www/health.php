<?php
header('Content-Type: application/json');

$checks = [];
$ok = true;

// DB: connect and verify schema is present
$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'sluchohry';
$user = getenv('DB_USER') ?: 'sluchohry';
$pass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass, [
        PDO::ATTR_TIMEOUT => 3,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->query('SELECT 1 FROM user LIMIT 1');
    $checks['db'] = 'ok';
} catch (Exception $e) {
    $checks['db'] = 'error';
    $ok = false;
}

// Filesystem: writable runtime dirs
foreach (['../sessions', '../log', '../temp'] as $dir) {
    if (!is_writable(__DIR__ . '/' . $dir)) {
        $checks['fs'] = 'error';
        $ok = false;
        break;
    }
}
if (!isset($checks['fs'])) {
    $checks['fs'] = 'ok';
}

http_response_code($ok ? 200 : 503);
echo json_encode(['status' => $ok ? 'ok' : 'error', 'app' => 'sluchohry', 'checks' => $checks]);
