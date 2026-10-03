<?php
$content = file_get_contents('resources/views/milestones/partials/details.blade.php');

$search = <<<EOT
    @if(\$milestone->template->has_chat)
    <!-- Alpine JS Modal for Messaging -->
    <div x-show="showMessageModal" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;" x-cloak>
EOT;

$replace = <<<EOT
    @if(\$milestone->template->has_chat)
    <!-- Alpine JS Modal for Messaging -->
    <template x-teleport="body">
    <div x-show="showMessageModal" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;" x-cloak>
EOT;

$content = str_replace($search, $replace, $content);

$search2 = <<<EOT
        </div>
    </div>
    @endif
</div>
EOT;

$replace2 = <<<EOT
        </div>
    </div>
    </template>
    @endif
</div>
EOT;

$content = str_replace($search2, $replace2, $content);

file_put_contents('resources/views/milestones/partials/details.blade.php', $content);
