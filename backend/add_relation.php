<?php
$content = file_get_contents("app/Models/Evaluation.php");

$old = "public function event()
    {
        return \$this->belongsTo(DefenceEvent::class, 'defence_event_id');
    }";

$new = "public function event()
    {
        return \$this->belongsTo(DefenceEvent::class, 'defence_event_id');
    }

    public function defenceEvent()
    {
        return \$this->belongsTo(DefenceEvent::class, 'defence_event_id');
    }";

$content = str_replace($old, $new, $content);
file_put_contents("app/Models/Evaluation.php", $content);
