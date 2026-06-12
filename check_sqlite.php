<?php
$project = __DIR__;
// Resolve DB path used by app: .env sets DB_DATABASE=../database.sqlite
$dbPath = $project . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database.sqlite';
echo "Project: $project\n";
echo "Resolved DB path: $dbPath\n";
$fullPath = realpath($dbPath) ?: $dbPath;
if (file_exists($fullPath)) {
    $info = stat($fullPath);
    echo "Exists. Size: " . filesize($fullPath) . " bytes\n";
} else {
    echo "File does not exist: $fullPath\n";
}

try {
    $pdo = new PDO('sqlite:' . $fullPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $res = $pdo->query("PRAGMA integrity_check;")->fetchAll(PDO::FETCH_COLUMN);
    echo "PRAGMA integrity_check:\n";
    foreach ($res as $r) echo " - $r\n";

    echo "\nsqlite_master tables:\n";
    $tables = $pdo->query("SELECT name, type FROM sqlite_master WHERE type IN ('table','index','view') ORDER BY type, name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tables as $t) echo " - {$t['type']}: {$t['name']}\n";
} catch (Throwable $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
}

?>