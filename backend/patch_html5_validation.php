<?php
$content = file_get_contents('resources/views/auth/register.blade.php');

// Add maxlengths
$content = str_replace('name="thesis_abstract" placeholder="Abstract (Optional)" rows="5"', 'name="thesis_abstract" placeholder="Abstract (Optional)" rows="5" maxlength="5000"', $content);
$content = str_replace('name="place_of_work" placeholder="Where do you currently work?"', 'name="place_of_work" placeholder="Where do you currently work?" maxlength="255"', $content);
$content = str_replace('name="phone_number" required placeholder="+234..."', 'name="phone_number" required placeholder="+234..." maxlength="20"', $content);
$content = str_replace('name="nationality" required placeholder="E.g. Nigerian"', 'name="nationality" required placeholder="E.g. Nigerian" maxlength="100"', $content);
$content = str_replace('name="matric_number" required placeholder="E.G. ACE26210011"', 'name="matric_number" required placeholder="E.G. ACE26210011" maxlength="255"', $content);
$content = str_replace('name="thesis_title" placeholder="Approved Thesis Title"', 'name="thesis_title" placeholder="Approved Thesis Title" maxlength="255"', $content);

file_put_contents('resources/views/auth/register.blade.php', $content);
echo "Patched HTML5 validation!\n";
