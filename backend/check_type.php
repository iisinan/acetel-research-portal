<?php
$content = file_get_contents("routes/web.php");
if (!str_contains($content, "test-defence-types")) {
    $code = <<<EOT

Route::get("/test-defence-types", function () {
    return \App\Models\MilestoneTemplate::pluck("defence_type")->unique();
});
EOT;
    file_put_contents("routes/web.php", $content . "\n" . $code);
}

