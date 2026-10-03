<?php
$content = file_get_contents('resources/views/milestones/partials/details.blade.php');

$search = <<<EOT
            @if(auth()->id() !== \$milestone->thesis->student->user_id)
            <button type="button" @click="showMessageModal = true; messageRecipient = '{{ addslashes(\$milestone->thesis->student->user->name) }}'" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl shadow-xl shadow-slate-200/40 transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="text-[10px] font-black uppercase tracking-widest hidden sm:inline">Message Student</span>
            </button>
            @endif
EOT;

$replace = <<<EOT
            @if(auth()->id() !== \$milestone->thesis->student->user_id && \$milestone->template->has_chat)
            <button type="button" @click="showMessageModal = true; messageRecipient = '{{ addslashes(\$milestone->thesis->student->user->name) }}'" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl shadow-xl shadow-slate-200/40 transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="text-[10px] font-black uppercase tracking-widest hidden sm:inline">Message Student</span>
            </button>
            @endif
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('resources/views/milestones/partials/details.blade.php', $content);
