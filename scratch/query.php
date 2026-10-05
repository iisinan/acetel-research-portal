<?php
foreach (['database/database.sqlite', 'backend/database/database.sqlite'] as $path) {
    if (!file_exists($path)) continue;
    echo "=== DB: $path ===\n";
    $pdo = new PDO("sqlite:$path");
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables (" . count($tables) . "): " . implode(', ', array_slice($tables, 0, 15)) . "\n";
    if (in_array('milestone_templates', $tables)) {
        $templates = $pdo->query("SELECT id, name, slug FROM milestone_templates")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($templates as $t) {
            $smCount = 0;
            if (in_array('student_milestones', $tables)) {
                $stmt = $pdo->prepare("SELECT count(*) FROM student_milestones WHERE milestone_template_id = ?");
                $stmt->execute([$t['id']]);
                $smCount = $stmt->fetchColumn();
            }
            echo "  Template: {$t['name']} (slug: {$t['slug']}) -> student_milestones: $smCount\n";
        }
    }
    if (in_array('defence_events', $tables)) {
        $events = $pdo->query("SELECT count(*), type FROM defence_events GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);
        echo "  Defence events by type: " . json_encode($events) . "\n";
    }
}
