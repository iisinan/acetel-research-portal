<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$stmt = $pdo->query("SELECT name, code FROM programs");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
