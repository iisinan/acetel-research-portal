<?php
$connections = [
    'sqlite_root' => new PDO('sqlite:database/database.sqlite'),
    'sqlite_backend' => new PDO('sqlite:backend/database/database.sqlite'),
];
foreach ($connections as $name => $pdo) {
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "$name: " . count($tables) . " tables. Has panel_members? " . (in_array('panel_members', $tables) ? 'YES' : 'NO') . "\n";
}

// Check PostgreSQL
$pgDbs = ['thesis_monitoring', 'laravel', 'postgres'];
foreach ($pgDbs as $db) {
    try {
        $pdo = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=$db", 'postgres', 'postgres');
        $tables = $pdo->query("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'")->fetchAll(PDO::FETCH_COLUMN);
        echo "pgsql $db: " . count($tables) . " tables. Has panel_members? " . (in_array('panel_members', $tables) ? 'YES' : 'NO') . "\n";
    } catch (\Throwable $e) {
        echo "pgsql $db error: " . $e->getMessage() . "\n";
    }
}
