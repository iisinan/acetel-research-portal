<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$template = \App\Models\MilestoneTemplate::where("slug", "like", "%seminar%")->first();
echo "Template: " . $template->name . " | Defence Type: " . $template->defence_type . "\n";

$event = \App\Models\DefenceEvent::first();
echo "Event type in DB: " . ($event ? $event->type : "None") . "\n";

$members = \App\Models\PanelMember::with("user")->get();
echo "Total Panel Members: " . $members->count() . "\n";
foreach($members->take(5) as $m) {
    echo "  - Role: {$m->role}, User: " . ($m->user ? $m->user->name : "Null") . ", Event: {$m->defence_event_id}\n";
}

