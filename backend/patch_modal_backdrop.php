<?php
$content = file_get_contents('resources/views/milestones/partials/details.blade.php');

$search1 = <<<EOT
            <div x-show="showMessageModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true"></div>
EOT;

$replace1 = <<<EOT
            <div x-show="showMessageModal" 
                 @click="showMessageModal = false"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm cursor-pointer" aria-hidden="true"></div>
EOT;

$content = str_replace($search1, $replace1, $content);

$search2 = <<<EOT
                 class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100"
                 @click.away="showMessageModal = false">
EOT;

$replace2 = <<<EOT
                 class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100 relative z-10">
EOT;

$content = str_replace($search2, $replace2, $content);

file_put_contents('resources/views/milestones/partials/details.blade.php', $content);
