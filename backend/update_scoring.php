<?php
$ctrl = file_get_contents("app/Http/Controllers/EvaluationController.php");
$ctrl = str_replace("max:10", "max:25", $ctrl);
file_put_contents("app/Http/Controllers/EvaluationController.php", $ctrl);

$create = file_get_contents("resources/views/evaluations/create.blade.php");
$create = str_replace("max=\"10\"", "max=\"25\"", $create);
$create = str_replace("placeholder=\"0-10\"", "placeholder=\"0-25\"", $create);
$create = str_replace("/ 10</span>", "/ 25</span>", $create);
file_put_contents("resources/views/evaluations/create.blade.php", $create);

$show = file_get_contents("resources/views/evaluations/show.blade.php");
$show = str_replace(">= 7", ">= 18", $show);
$show = str_replace(">= 5", ">= 12", $show);
$show = str_replace("/ 10</span>", "/ 25</span>", $show);
file_put_contents("resources/views/evaluations/show.blade.php", $show);

$pdf = file_get_contents("resources/views/pdf/evaluation.blade.php");
$pdf = str_replace("/ 10</div>", "/ 25</div>", $pdf);
file_put_contents("resources/views/pdf/evaluation.blade.php", $pdf);

