<?php
\ = [
    'resources/views/dashboard/coordinator.blade.php',
    'resources/views/dashboard/director.blade.php',
    'resources/views/dashboard/supervisor.blade.php',
    'resources/views/dashboard/student.blade.php'
];

foreach (\ as \) {
    if (file_exists(\)) {
        \ = file_get_contents(\);
        
        \ = str_replace('\->student->user->name', '\->student?->user?->name ?? \'Unknown\'', \);
        \ = str_replace('\->student->program->code', '\->student?->program?->code ?? \'N/A\'', \);
        
        \ = str_replace('\->thesis->student->user->name', '\->thesis?->student?->user?->name ?? \'Unknown\'', \);
        
        \ = str_replace('\->user->name', '\->user?->name ?? \'Unknown\'', \);
        \ = str_replace('\->program->name', '\->program?->name ?? \'Unknown\'', \);
        \ = str_replace('\->program->code', '\->program?->code ?? \'N/A\'', \);
        
        \ = str_replace('\->user->name', '\->user?->name ?? \'Unknown\'', \);
        \ = str_replace('\->user->email', '\->user?->email ?? \'Unknown\'', \);
        \ = str_replace('\->staff_id', '\->staff_id ?? \'N/A\'', \);
        
        file_put_contents(\, \);
    }
}

