<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in database/database.sqlite:\n" . implode("\n", $tables) . "\n";
