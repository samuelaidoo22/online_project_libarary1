<?php
require_once __DIR__ . '/config/db.php';

$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    migration VARCHAR(255) PRIMARY KEY,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);
$check = $pdo->prepare('SELECT 1 FROM migrations WHERE migration = ?');
$record = $pdo->prepare('INSERT INTO migrations (migration) VALUES (?)');

foreach ($files as $file) {
    $name = basename($file);
    $check->execute([$name]);
    if ($check->fetchColumn()) {
        continue;
    }

    try {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException('Unable to read migration: ' . $name);
        }
        $pdo->exec($sql);
        $record->execute([$name]);
        echo "Applied: {$name}\n";
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

echo "Migrations complete.\n";
