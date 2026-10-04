<?php
$content = file_get_contents("routes/web.php");
if (!str_contains($content, "test-json-sql")) {
    $code = <<<EOT

Route::get("/test-json-sql", function () {
    try {
        \$q = \App\Models\StudentMilestone::whereJsonDoesntContain("approvals", ["user_id" => 1]);
        return \$q->toSql();
    } catch (\Throwable \$e) {
        return \$e->getMessage();
    }
});
EOT;
    file_put_contents("routes/web.php", $content . "\n" . $code);
}

