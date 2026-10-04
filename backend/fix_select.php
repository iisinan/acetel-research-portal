<?php
$content = file_get_contents("resources/views/admin/milestone-templates/index.blade.php");

// Remove name="..." from the checkbox
$old_checkbox = "<input type=\"checkbox\" :value=\"option.id\" x-model=\"selected\" name=\"supervisor_profile_ids[]\" class=\"rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 mr-2 w-3.5 h-3.5\">";
$new_checkbox = "<input type=\"checkbox\" :value=\"option.id\" x-model=\"selected\" class=\"rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 mr-2 w-3.5 h-3.5\">";
$content = str_replace($old_checkbox, $new_checkbox, $content);

// Add hidden inputs for the selected array only in the assign-examiner-global form
$old_csrf = "@submit=\"if(selected.length === 0) { (window.toast ? window.toast.warning('Please select at least one examiner.') : alert('Please select at least one examiner.')); \$event.preventDefault(); }\">
                                                @csrf";
$new_csrf = "@submit=\"if(selected.length === 0) { (window.toast ? window.toast.warning('Please select at least one examiner.') : alert('Please select at least one examiner.')); \$event.preventDefault(); }\">
                                                @csrf\n                                                  <template x-for=\"id in selected\">\n                                                      <input type=\"hidden\" name=\"supervisor_profile_ids[]\" :value=\"id\">\n                                                  </template>";
$content = str_replace($old_csrf, $new_csrf, $content);

file_put_contents("resources/views/admin/milestone-templates/index.blade.php", $content);

