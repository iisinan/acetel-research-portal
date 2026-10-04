@extends(auth()->user()->hasRole('Admin') ? 'layouts.admin' : (auth()->user()->hasRole('Program Coordinator') ? 'layouts.coordinator' : 'layouts.dashboard'))

@section('header')
    Communications Hub
@endsection

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }
    /* Whatsapp-like subtle background pattern for chat area */
    .chat-bg {
        background-color: #f0f2f5;
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%239C92AC' fill-opacity='0.05' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
    }
</style>

<div x-data="{ 
    newModalOpen: false,
    searchQuery: '',
    filterTab: 'all',
    selectedRecipientId: '',
    selectedRecipientName: '',
    attachedFileName: '',
    chatAttachedFileName: '',
    mobileShowChat: {{ $selectedPartner ? 'true' : 'false' }}
}" class="h-[calc(100vh-8rem)] min-h-[600px] flex flex-col pb-4 animate-in-up">

    {{-- Main Chat App Container --}}
    <div class="flex-1 bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col md:flex-row relative">
        
        {{-- LEFT PANEL: Conversations List --}}
        <div class="w-full md:w-[350px] lg:w-[400px] shrink-0 border-r border-slate-200 flex flex-col bg-white z-10"
             :class="mobileShowChat ? 'hidden md:flex' : 'flex h-full'">
            
            {{-- Left Header --}}
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/50">
                <h2 class="text-xl font-black text-slate-800 tracking-tight">Messages</h2>
                <div class="flex items-center gap-2">
                    @if($unreadCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">
                            {{ $unreadCount }} new
                        </span>
                    @endif
                    <button type="button" @click="newModalOpen = true" class="p-2 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors cursor-pointer shadow-xs" title="New Message">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                </div>
            </div>

            {{-- Search & Filters --}}
            <div class="px-5 py-3 border-b border-slate-100 space-y-3 shrink-0 bg-white">
                <div class="relative group">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 group-focus-within:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Search or start new chat" 
                           class="w-full pl-10 pr-4 py-2 bg-slate-100/80 hover:bg-slate-100 focus:bg-white border border-transparent focus:border-emerald-500/50 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-500 outline-none transition-all shadow-none" />
                </div>

                {{-- Filter Segmented Control --}}
                <div class="flex items-center gap-1 p-1 bg-slate-100/70 rounded-xl text-xs font-bold">
                    <button type="button" @click="filterTab = 'all'" 
                            :class="filterTab === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer">
                        All
                    </button>
                    <button type="button" @click="filterTab = 'unread'" 
                            :class="filterTab === 'unread' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        Unread
                        @if($unreadCount > 0)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Conversations List --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                @forelse($conversations as $conv)
                    @php
                        $partner = $conv->partner;
                        $isSelected = $selectedPartner && $selectedPartner->id === $partner->id;
                        $isSentByMe = $conv->last_message->sender_id === auth()->id();
                    @endphp

                    <a href="{{ route('inbox.index', ['user_id' => $partner->id]) }}" 
                       x-show="(filterTab === 'all' || {{ $conv->unread_count > 0 ? 'true' : 'false' }}) && 
                               ('{{ strtolower($partner->name) }}'.includes(searchQuery.toLowerCase()) || 
                                '{{ strtolower($conv->last_message->subject) }}'.includes(searchQuery.toLowerCase()) || 
                                '{{ strtolower(strip_tags($conv->last_message->body)) }}'.includes(searchQuery.toLowerCase()))"
                       class="flex items-center gap-3.5 px-5 py-3 transition-colors cursor-pointer {{ $isSelected ? 'bg-slate-50/80' : 'hover:bg-slate-50/50' }}">
                        
                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-black text-slate-600 bg-slate-200 border border-slate-300/50">
                                {{ strtoupper(collect(explode(' ', $partner->name))->map(fn($n) => substr($n, 0, 1))->take(2)->implode('')) }}
                            </div>
                            @if($conv->unread_count > 0)
                                <span class="absolute top-0.5 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                            @endif
                        </div>

                        {{-- Text content --}}
                        <div class="flex-1 min-w-0 flex flex-col justify-center h-12 border-b border-slate-100/70 pb-1 {{ $loop->last ? 'border-b-0' : '' }}">
                            <div class="flex items-center justify-between mb-0.5 gap-2">
                                <h4 class="text-[15px] leading-tight font-bold truncate {{ $conv->unread_count > 0 ? 'text-slate-900' : 'text-slate-800' }}">
                                    {{ $partner->name }}
                                </h4>
                                <span class="text-[11px] font-medium whitespace-nowrap {{ $conv->unread_count > 0 ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">
                                    {{ $conv->last_message_at->shortAbsoluteDiffForHumans() }}
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[13px] leading-snug truncate {{ $conv->unread_count > 0 ? 'font-semibold text-slate-800' : 'text-slate-500' }}">
                                    @if($isSentByMe)
                                        <span class="text-slate-400">You: </span>
                                    @endif
                                    {{ Str::limit(strip_tags($conv->last_message->body), 50) }}
                                </p>
                                @if($conv->unread_count > 0)
                                    <span class="shrink-0 bg-emerald-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center">
                                        {{ $conv->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-400 text-sm">
                        No conversations found.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT PANEL: Active Chat Workspace --}}
        <div class="flex-1 flex flex-col chat-bg relative" :class="!mobileShowChat ? 'hidden md:flex' : 'flex h-full'">
            @if($selectedPartner)
                @php
                    $partnerRole = $selectedPartner->roles->first()?->name ?? 'User';
                @endphp
                
                {{-- Chat Header --}}
                <div class="h-[68px] px-5 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 z-10 shadow-xs">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="mobileShowChat = false" class="md:hidden -ml-2 p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                        </button>

                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-black bg-slate-200 text-slate-700 border border-slate-300/50">
                            {{ strtoupper(collect(explode(' ', $selectedPartner->name))->map(fn($n) => substr($n, 0, 1))->take(2)->implode('')) }}
                        </div>

                        <div class="flex flex-col justify-center">
                            <h3 class="text-[15px] font-bold text-slate-900 leading-none mb-1">{{ $selectedPartner->name }}</h3>
                            <p class="text-[12px] font-medium text-slate-500 leading-none">
                                {{ $partnerRole }} • {{ $selectedPartner->email }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Chat Messages Area --}}
                <div class="flex-1 overflow-y-auto p-4 sm:px-10 sm:py-6 custom-scrollbar flex flex-col gap-3"
                     x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })">
                    
                    @if($chatMessages->isEmpty())
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="bg-white/80 backdrop-blur rounded-2xl p-6 shadow-sm border border-slate-200 max-w-sm">
                                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900">Start of conversation</h4>
                                <p class="text-xs text-slate-500 mt-1">Send a message to {{ $selectedPartner->name }} to begin.</p>
                            </div>
                        </div>
                    @else
                        @php
                            $previousDate = null;
                        @endphp

                        @foreach($chatMessages as $msg)
                            @php
                                $msgDate = $msg->created_at->format('Y-m-d');
                                $isFromMe = $msg->sender_id === auth()->id();
                            @endphp

                            {{-- Date Separator Pill --}}
                            @if($previousDate !== $msgDate)
                                <div class="flex items-center justify-center my-3 sticky top-2 z-10">
                                    <span class="px-3 py-1 rounded-lg bg-white/90 backdrop-blur-sm text-slate-600 text-[11px] font-bold shadow-xs border border-slate-200 uppercase tracking-wide">
                                        {{ $msg->created_at->isToday() ? 'Today' : ($msg->created_at->isYesterday() ? 'Yesterday' : $msg->created_at->format('M d, Y')) }}
                                    </span>
                                </div>
                                @php $previousDate = $msgDate; @endphp
                            @endif

                            {{-- Message Bubble Wrapper --}}
                            <div class="flex w-full {{ $isFromMe ? 'justify-end' : 'justify-start' }}">
                                <div class="flex max-w-[85%] md:max-w-[70%] flex-col relative group">
                                    
                                    <div class="relative px-4 py-3 rounded-2xl shadow-sm border {{ $isFromMe ? 'bg-[#dcf8c6] border-[#c0e8a8] rounded-tr-sm' : 'bg-white border-slate-200 rounded-tl-sm' }}">
                                        
                                        {{-- Subject --}}
                                        @if($msg->subject)
                                            <div class="text-[13px] font-black {{ $isFromMe ? 'text-emerald-900' : 'text-slate-900' }} mb-1 border-b {{ $isFromMe ? 'border-emerald-800/10' : 'border-slate-100' }} pb-1">
                                                {{ $msg->subject }}
                                            </div>
                                        @endif
                                        
                                        {{-- Body --}}
                                        <div class="text-[14px] leading-relaxed whitespace-pre-wrap {{ $isFromMe ? 'text-slate-800' : 'text-slate-800' }}">
                                            {!! nl2br(e($msg->body)) !!}
                                        </div>

                                        {{-- Attachments --}}
                                        @if($msg->attachments->count() > 0)
                                            <div class="mt-2 space-y-1">
                                                @foreach($msg->attachments as $attachment)
                                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" 
                                                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold {{ $isFromMe ? 'bg-emerald-100/50 text-emerald-800 hover:bg-emerald-200/50' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} transition-colors">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                        <span class="truncate max-w-[150px]">{{ $attachment->file_name }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        {{-- Timestamp (Inside Bubble) --}}
                                        <div class="flex items-center justify-end mt-1 gap-1 {{ $isFromMe ? 'text-emerald-700/80' : 'text-slate-400' }}">
                                            <span class="text-[10px] font-medium">
                                                {{ $msg->created_at->format('H:i') }}
                                            </span>
                                            @if($isFromMe)
                                                {{-- Read receipt ticks placeholder --}}
                                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Chat Input Bar --}}
                <div class="p-3 bg-[#f0f2f5] border-t border-slate-200 shrink-0">
                    <form action="{{ route('inbox.store') }}" method="POST" enctype="multipart/form-data" x-ref="chatForm" class="flex flex-col gap-2 max-w-5xl mx-auto">
                        @csrf
                        <input type="hidden" name="to[]" value="{{ $selectedPartner->id }}">
                        
                        @php
                            $defaultSubject = $chatMessages->last()?->subject;
                            if($defaultSubject && !str_starts_with(strtolower($defaultSubject), 're:')) {
                                $defaultSubject = 'Re: ' . $defaultSubject;
                            }
                        @endphp
                        <input type="hidden" name="subject" value="{{ $defaultSubject }}">

                        {{-- Attachment Tag Preview --}}
                        <div x-show="chatAttachedFileName" x-cloak class="flex items-center gap-2 p-2 bg-white rounded-xl shadow-xs text-xs text-slate-700 w-fit ml-12">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="font-bold truncate max-w-[200px]" x-text="chatAttachedFileName"></span>
                            <button type="button" @click="$refs.chatAttachmentInput.value = ''; chatAttachedFileName = ''" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Composer Container --}}
                        <div class="flex items-end gap-2">
                            <label class="p-3 text-slate-500 hover:text-slate-700 rounded-full cursor-pointer transition-colors shrink-0 flex items-center justify-center mb-0.5" title="Attach file">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <input type="file" 
                                       name="attachments[]" 
                                       x-ref="chatAttachmentInput" 
                                       @change="chatAttachedFileName = $event.target.files[0] ? $event.target.files[0].name : ''" 
                                       class="hidden" />
                            </label>

                            <div class="flex-1 bg-white rounded-2xl flex items-end shadow-sm">
                                <textarea name="body" 
                                          required
                                          rows="1"
                                          x-data="{ resize() { $el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px' } }"
                                          x-init="resize()"
                                          @input="resize()"
                                          placeholder="Type a message"
                                          class="flex-1 bg-transparent border-none text-[15px] text-slate-800 placeholder-slate-400 focus:ring-0 outline-none resize-none py-3 px-4 overflow-y-auto custom-scrollbar rounded-2xl"
                                          @keydown.enter.prevent="if(!$event.shiftKey && $el.value.trim() !== '') { $refs.chatForm.submit(); }"></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-12 h-12 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shrink-0 cursor-pointer flex items-center justify-center shadow-sm mb-0.5">
                                <svg class="w-5 h-5 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- Empty Selection State --}}
                <div class="h-full flex flex-col items-center justify-center text-center p-8 chat-bg">
                    <div class="w-32 h-32 mb-6">
                        <svg class="w-full h-full text-slate-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="20" y="25" width="60" height="50" rx="10" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M40 45H60" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M40 55H50" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M30 35H70" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-light text-slate-600 mb-2">Thesis Monitoring Web</h3>
                    <p class="text-[15px] text-slate-400 max-w-sm font-medium">Select a conversation to start chatting, or click New Message to compose a new thread.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- + New Message Modal Dialog --}}
    <div x-show="newModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="newModalOpen = false">
        
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden animate-in-up"
             @click.outside="newModalOpen = false">
            
            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-emerald-100 text-emerald-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-slate-900 leading-tight">New Message</h3>
                        <p class="text-[12px] font-medium text-slate-500">Send a direct message</p>
                    </div>
                </div>

                <button type="button" 
                        @click="newModalOpen = false" 
                        class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Form --}}
            <form method="POST" action="{{ route('inbox.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                
                {{-- Recipient Select --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">To</label>
                    <select name="to[]" 
                            required 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all appearance-none">
                        <option value="">Select recipient...</option>
                        @foreach($availableRecipients as $rec)
                            <option value="{{ $rec->id }}">
                                {{ $rec->name }} ({{ $rec->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Input --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject (Topic)</label>
                    <input type="text" 
                           name="subject" 
                           placeholder="e.g. Milestone Correction" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all" />
                </div>

                {{-- Message Body --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Message</label>
                    <textarea name="body" 
                              rows="4" 
                              required
                              placeholder="Type your message..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-[14px] text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all resize-none"></textarea>
                </div>

                {{-- Attachment --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Attachment</label>
                    <input type="file" 
                           name="attachments[]" 
                           x-ref="modalAttachmentInput"
                           @change="attachedFileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                    
                    <div x-show="attachedFileName" x-cloak class="flex items-center gap-2 mt-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700">
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span class="font-semibold truncate" x-text="attachedFileName"></span>
                        <button type="button" @click="$refs.modalAttachmentInput.value = ''; attachedFileName = ''" class="ml-auto text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="pt-4 flex items-center justify-end gap-3 mt-2">
                    <button type="button" 
                            @click="newModalOpen = false" 
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition-colors shadow-sm cursor-pointer">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
