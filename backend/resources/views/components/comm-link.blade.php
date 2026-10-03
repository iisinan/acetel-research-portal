@props([
    'messages',
    'thesisId',
    'milestoneId' => null,
    'potentialMentions' => null,
    'title' => 'Guidance Comm-Link',
    'height' => '500px',
    'maxHeight' => '700px'
])

<div class="h-full flex flex-col !p-0 overflow-hidden bg-white rounded-2xl shadow-premium border border-slate-100 transition-all duration-500"
     :class="isMinimized ? 'h-[60px]' : ''"
     x-data="{ 
        isMinimized: false,
        replyingTo: null, 
        replyContent: '', 
        showAttachments: false,
        isSending: false,
        isMentioning: false,
        mentionSearch: '',
        scrollToMessage(id) {
            const el = document.getElementById('msg-' + id);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },
        handleInput(e) {
            const textarea = e.target;
            textarea.style.height = ''; 
            textarea.style.height = textarea.scrollHeight + 'px';

            const cursorPosition = textarea.selectionStart;
            const textBeforeCursor = this.replyContent.substring(0, cursorPosition);
            const lastAt = textBeforeCursor.lastIndexOf('@');

            if (lastAt !== -1 && (lastAt === 0 || textBeforeCursor[lastAt - 1] === ' ')) {
                this.isMentioning = true;
                this.mentionSearch = textBeforeCursor.substring(lastAt + 1);
            } else {
                this.isMentioning = false;
                this.mentionSearch = '';
            }
        },
        selectMention(email) {
            const textarea = this.$refs.textarea;
            const cursorPosition = textarea.selectionStart;
            const textBeforeCursor = this.replyContent.substring(0, cursorPosition);
            const textAfterCursor = this.replyContent.substring(cursorPosition);
            const lastAt = textBeforeCursor.lastIndexOf('@');

            this.replyContent = textBeforeCursor.substring(0, lastAt) + '@' + email + ' ' + textAfterCursor;
            this.isMentioning = false;
            this.mentionSearch = '';
            
            this.$nextTick(() => {
                textarea.focus();
                const newPos = lastAt + email.length + 2;
                textarea.setSelectionRange(newPos, newPos);
            });
        },
        selectFirstMention() {
            // Select the first visible mention in the dropdown
            const firstMention = this.$el.querySelector('.mention-item:not([style*=\'display: none\'])');
            if (firstMention) {
                firstMention.click();
                return true;
            }
            return false;
        },
        async sendMessage(e) {
            const form = e instanceof HTMLFormElement ? e : (e.target.tagName === 'FORM' ? e.target : e.target.closest('form'));
            if (!form) return;
            
            const hasFiles = Array.from(form.querySelectorAll('input[type=file]')).some(input => input.files.length > 0);
            if (this.isSending || (!this.replyContent.trim() && !hasFiles)) return;
            
            this.isMinimized = false;
            this.isSending = true;
            const formData = new FormData(form);
            
            try {
                const response = await fetch('{{ route('messages.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const stream = this.$refs.stream;
                    
                    // Append Message HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    stream.appendChild(tempDiv.firstElementChild);
                    
                    // Reset State
                    this.replyContent = '';
                    this.replyingTo = null;
                    this.showAttachments = false;
                    form.querySelectorAll('input[type=file]').forEach(input => input.value = '');
                    
                    // Scroll to bottom
                    this.$nextTick(() => {
                        stream.scrollTop = stream.scrollHeight;
                    });
                } else {
                    const error = await response.json();
                    (window.toast ? window.toast.error(error.message || 'Failed to send message') : alert(error.message || 'Failed to send message'));
                }
            } catch (error) {
                console.error('Send Error:', error);
                (window.toast ? window.toast.error('Connection error. Please try again.') : alert('Connection error. Please try again.'));
            } finally {
                this.isSending = false;
            }
        },
        init() {
            this.$nextTick(() => {
                const stream = this.$refs.stream;
                if (stream) stream.scrollTop = stream.scrollHeight;
            });
        }
     }">
    <div class="px-6 py-3 border-b border-slate-50 flex items-center justify-between bg-slate-50/30 cursor-pointer select-none" @click="isMinimized = !isMinimized">
        <div class="flex items-center gap-3">
            <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">{{ $title }}</h3>
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[8px] font-black text-emerald-600 uppercase tracking-tighter">Channel Active</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <template x-if="!isMinimized">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Minimize</span>
            </template>
            <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary-600 transition-colors shadow-sm">
                <svg class="w-4 h-4 transition-transform duration-300" :class="isMinimized ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>

    <div x-show="!isMinimized" x-cloak class="flex-1 flex flex-col bg-[#efeae2] relative overflow-hidden" style="min-h: {{ $height }}; max-h: {{ $maxHeight }};">
        <!-- Chat Background Pattern -->
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none bg-[radial-gradient(#000_1px,transparent_1px)] [background-size:16px_16px]"></div>

        <!-- Messages Stream -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2.5 custom-scrollbar relative z-10" x-ref="stream" id="chat-stream-{{ $milestoneId ?? 'global' }}">
            @php $lastDate = null; @endphp
            
            @forelse($messages as $message)
                @php
                    $isMine = $message->user_id === Auth::id();
                    $currentDate = $message->created_at->format('F j, Y');
                    $showDate = $lastDate !== $currentDate;
                    $lastDate = $currentDate;
                @endphp

                @if($showDate)
                    <div class="flex justify-center my-2 transition-all duration-300">
                        <span class="px-2 py-0.5 bg-white/80 backdrop-blur-sm rounded-lg text-[8px] font-black text-slate-500 uppercase tracking-widest shadow-sm border border-slate-100 italic">
                            {{ $currentDate }}
                        </span>
                    </div>
                @endif

                <div id="msg-{{ $message->id }}" class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }} group animate-in-up mb-1.5 last:mb-0">
                    <div class="relative max-w-[85%] sm:max-w-[70%] {{ $isMine ? 'bg-[#dcf8c6] rounded-l-xl rounded-tr-xl' : 'bg-white rounded-r-xl rounded-tl-xl' }} px-3 py-1.5 shadow-sm border border-slate-200/50">
                        
                        <!-- WhatsApp Context Chevron (Hover) -->
                        <div class="absolute top-0.5 right-1 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer z-30">
                            <button @click="replyingTo = { id: '{{ $message->id }}', name: '{{ addslashes($message->sender?->name ?? 'User') }}', content: '{{ addslashes(Str::limit($message->content, 50)) }}' }" 
                                    class="p-0.5 hover:bg-black/5 rounded text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </div>

                        <!-- Quoted Message (if reply) -->
                        @if($message->replyTo)
                            <div @click="scrollToMessage('{{ $message->reply_to_id }}')" class="cursor-pointer mb-2 p-2 bg-black/5 rounded-lg border-l-4 border-emerald-500 text-xs">
                                <p class="font-black text-emerald-700 uppercase tracking-tighter text-[9px]">{{ $message->replyTo->sender?->name ?? 'User' }}</p>
                                <p class="text-slate-600 truncate">{{ $message->replyTo->content }}</p>
                            </div>
                        @endif

                        @if(!$isMine)
                            <p class="text-[10px] font-black text-emerald-600 border-b border-emerald-50 mb-1 pb-1 tracking-tight">{{ $message->sender?->name ?? 'User' }}</p>
                        @endif

                        <!-- Message Type: File -->
                        @if($message->type === 'file' && $message->file_path)
                            <div class="mb-2 rounded-lg overflow-hidden border border-slate-200 bg-white">
                                @php
                                    $ext = strtolower(pathinfo($message->file_path, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                @endphp
                                
                                @if($isImage)
                                    <button type="button" @click.prevent="$dispatch('open-document-preview', { url: '{{ Storage::url($message->file_path) }}', title: '{{ addslashes($message->meta['file_name'] ?? 'Image Attachment') }}', type: 'image' })" class="w-full text-left cursor-pointer transition-transform hover:opacity-90">
                                        <img src="{{ Storage::url($message->file_path) }}" class="w-full h-auto max-h-60 object-cover" alt="Attachment">
                                    </button>
                                @else
                                    <div class="p-3 flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ $message->meta['file_name'] ?? 'Document' }}</p>
                                            <p class="text-[9px] text-slate-400 font-black uppercase">{{ strtoupper($ext) }} • {{ number_format(($message->meta['file_size'] ?? 0) / 1024, 1) }} KB</p>
                                        </div>
                                        <button type="button" title="Preview" @click.prevent="$dispatch('open-document-preview', { url: '{{ Storage::url($message->file_path) }}', title: '{{ addslashes($message->meta['file_name'] ?? 'Document Attachment') }}' })" class="p-2 text-slate-400 hover:text-emerald-500 transition-colors cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </button>
                                        <a href="{{ Storage::url($message->file_path) }}" download title="Download" class="p-2 text-slate-400 hover:text-emerald-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <p class="text-[14px] leading-relaxed text-slate-800 whitespace-pre-wrap pr-10">{!! preg_replace('/@([a-zA-Z0-9_\-\.\+]+@[a-zA-Z0-9_\-\.]+)/', '<span class="text-acetel-600 font-bold">@$1</span>', $message->content) !!}</p>
                        
                        <div class="flex items-center justify-end gap-1 mt-1">
                            <p class="text-[10px] text-slate-500/70">{{ $message->created_at->format('H:i') }}</p>
                            @if($isMine)
                                <svg class="w-4 h-4 text-[#53bdeb]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 12l4 4L18 6m-5 6l4 4L22 6" /></svg>
                            @endif
                        </div>

                        <!-- Bubble Tail -->
                        <div class="absolute top-0 {{ $isMine ? '-right-2' : '-left-2' }} w-3 h-3">
                            <svg viewBox="0 0 10 10" class="{{ $isMine ? 'text-[#dcf8c6]' : 'text-white' }} fill-current">
                                @if($isMine) <path d="M0 0 L10 0 L10 10 Z" /> @else <path d="M0 0 L10 0 L0 10 Z" /> @endif
                            </svg>
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-10 opacity-30 grayscale">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="text-xs font-black text-slate-900 uppercase tracking-widest">Protocol Initialized</p>
                    <p class="text-[10px] font-bold text-slate-500">Secure institutional transmission ready.</p>
                </div>
            @endforelse
        </div>

        <!-- Input Area -->
        <div class="bg-[#f0f2f5] px-4 py-2 border-t border-slate-200 z-20">
            <!-- Reply Context -->
            <template x-if="replyingTo">
                <div class="mb-2 p-3 bg-white border-l-4 border-emerald-500 rounded-lg flex items-center justify-between shadow-sm animate-in-up">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-tight" x-text="'Replying to ' + replyingTo.name"></p>
                        <p class="text-xs text-slate-500 truncate" x-text="replyingTo.content"></p>
                    </div>
                    <button @click="replyingTo = null" class="p-1 hover:bg-slate-100 rounded-full text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </template>

            <form @submit.prevent="sendMessage" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="thesis_project_id" value="{{ $thesisId }}">
                @if($milestoneId)
                    <input type="hidden" name="student_milestone_id" value="{{ $milestoneId }}">
                @endif
                <input type="hidden" name="reply_to_id" :value="replyingTo ? replyingTo.id : ''">
                
                <!-- Emoji Placeholder (WhatsApp UI) -->
                <button type="button" class="text-slate-500 hover:text-emerald-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>

                <!-- Attachment Toggle -->
                <div class="relative">
                    <button type="button" @click="showAttachments = !showAttachments" 
                            class="text-slate-500 hover:text-emerald-600 transition-colors"
                            :class="{ 'text-emerald-600': showAttachments }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                    
                    <div x-show="showAttachments" @click.away="showAttachments = false" 
                         x-transition class="absolute bottom-full left-0 mb-4 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 flex flex-col gap-1 w-40 z-50">
                        <label class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-xl cursor-pointer">
                            <input type="file" name="file" x-ref="fileInput" accept=".pdf" class="hidden" @change="sendMessage($event.target.closest('form'))">
                            <div class="w-8 h-8 rounded-full bg-acetel-500 text-white flex items-center justify-center shadow-md">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-700">Photos</span>
                        </label>
                        <label class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-xl cursor-pointer">
                            <input type="file" name="file" accept=".pdf" class="hidden" @change="sendMessage($event.target.closest('form'))">
                            <div class="w-8 h-8 rounded-full bg-[#7f66ff] text-white flex items-center justify-center shadow-md">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A1 1 0 0113.293 2.707l5.414 5.414A1 1 0 0119 8.828V16a2 2 0 01-2 2H5a2 2 0 01-2-2V4z"></path></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-700">Document</span>
                        </label>
                    </div>
                </div>

                <div class="flex-1 bg-white rounded-xl px-4 py-2 shadow-sm relative">
                    <!-- Mention Dropdown -->
                    <div x-show="mentionSearch.length > 0 || isMentioning" 
                         x-cloak
                         class="absolute bottom-full left-0 mb-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 animate-in-up">
                        <div class="px-4 py-2 border-b border-slate-50">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Tag Scholar</p>
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                            @php
                                // Ensure we have potential mentions. If not passed, we'll try to find them or suggest coordinators/supervisors.
                                $potentialMentions = $potentialMentions ?? (\App\Models\User::role(['Supervisor', 'Program Coordinator', 'Student'])->take(10)->get());
                            @endphp
                            @foreach($potentialMentions as $pUser)
                                <button type="button" 
                                        @mousedown.prevent="selectMention('{{ $pUser->email }}')"
                                        x-show="mentionSearch === '' || '{{ strtolower($pUser->name) }}'.includes(mentionSearch.toLowerCase())"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors text-left group mention-item">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors">
                                        {{ substr($pUser->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ $pUser->name }}</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ $pUser->email }}</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <textarea name="content" x-model="replyContent" rows="1" x-ref="textarea"
                              @keydown.enter.prevent="if(isMentioning) { if(!selectFirstMention()) sendMessage($event.target.closest('form')) } else { if($event.shiftKey) { replyContent += '\n' } else { sendMessage($event.target.closest('form')) } }"
                              @input="handleInput($event)"
                              placeholder="Type a message..." 
                              class="w-full focus:ring-0 border-none bg-transparent py-1 text-sm text-slate-800 placeholder-slate-500 resize-none max-h-32"></textarea>
                </div>

                <button type="submit" 
                        class="text-slate-500 hover:text-emerald-600 transition-all active:scale-90"
                        :class="{ 'text-emerald-600': (replyContent.trim().length > 0 || isSending), 'opacity-50': isSending }"
                        :disabled="isSending">
                    <template x-if="!isSending">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M1.101 21.757L23.8 12.028 1.101 2.3l.011 7.912 13.623 1.816-13.623 1.817-.011 7.912z" /></svg>
                    </template>
                    <template x-if="isSending">
                        <svg class="w-6 h-6 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><circle cx="12" cy="12" r="10" stroke-opacity="0.2"></circle><path d="M12 2a10 10 0 0110 10" stroke-linecap="round"></path></svg>
                    </template>
                </button>
            </form>
        </div>
    </div>
</div>
