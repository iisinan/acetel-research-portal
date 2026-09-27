<?php
$content = file_get_contents('app/Http/Controllers/Admin/DocumentTemplateController.php');

$oldValidationStore = "'file' => 'required|file|mimes:pdf,doc,docx,odt,ppt,pptx,xls,xlsx,txt,jpg,png,jpeg|max:10240', // 10MB";
$newValidationStore = "'file' => 'required|file|mimes:pdf|max:10240', // 10MB";
$content = str_replace($oldValidationStore, $newValidationStore, $content);

$oldValidationUpdate = "'file' => 'nullable|file|mimes:pdf,doc,docx,odt,ppt,pptx,xls,xlsx,txt,jpg,png,jpeg|max:10240',";
$newValidationUpdate = "'file' => 'nullable|file|mimes:pdf|max:10240',";
$content = str_replace($oldValidationUpdate, $newValidationUpdate, $content);

file_put_contents('app/Http/Controllers/Admin/DocumentTemplateController.php', $content);
echo "Patched document templates!\n";
