@extends(auth()->user()->hasRole('Admin') ? 'layouts.admin' : (auth()->user()->hasRole('Program Coordinator') ? 'layouts.coordinator' : 'layouts.dashboard'))

@section('header')
    Communications Hub
@endsection

@section('content')
<div x-data="{ 
    newModalOpen: false,
    searchQuery: '',
    filterTab: 'all',
    selectedRecipientId: '',
    selectedRecipientName: '',
    attachedFileName: '',
    chatAttachedFileName: '',
    mobileShowChat: {{ $selectedPartner ? 'true' : 'false' }}
}" class="space-y-6 animate-in-up pb-8">

    {{-- Top Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200/80 rounded-3xl p-6 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5 mb-1.5 text-emerald-600">
                <div class="p-1.5 rounded-xl bg-emerald-50 border border-emerald-100">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400">Direct Chat & Messaging</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Communications Hub</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-500 mt-1">Real-time messaging and conversation history with students, supervisors, and coordinators.</p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            @if($unreadCount > 0)
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-emerald-50 border border-emerald-200/60 text-emerald-700 text-xs font-bold shadow-xs">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>{{ $unreadCount }} Unread {{ Str::plural('Message', $unreadCount) }}</span>
                </div>
            @endif

            <button type="button" 
                    @click="newModalOpen = true" 
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs uppercase tracking-wider transition-all shadow-sm hover:shadow-emerald-500/20 flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>New Message</span>
            </button>
        </div>
    </div>

    {{-- Main Chat Workspace (2-Column Layout) --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex h-[calc(100vh-14rem)] min-h-[620px] max-h-[820px]">
        
        {{-- Left Panel: Conversations List --}}
        <div class="w-full md:w-80 lg:w-96 shrink-0 border-r border-slate-100 flex flex-col bg-slate-50/40"
             :class="mobileShowChat ? 'hidden md:flex' : 'flex'">
            
            {{-- Search & Filter Header --}}
            <div class="p-4 border-b border-slate-100 space-y-3 bg-white">
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Search conversations..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 hover:bg-slate-100/60 focus:bg-white border border-slate-200 focus:border-emerald-500 rounded-2xl text-xs font-semibold text-slate-800 placeholder-slate-400 outline-none transition-all" />
                </div>

                {{-- Filter Pills --}}
                <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl text-xs font-bold">
                    <button type="button" 
                            @click="filterTab = 'all'" 
                            :class="filterTab === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
                            class="flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer">
                        All Chats ({{ $conversations->count() }})
                    </button>
                    <button type="button" 
                            @click="filterTab = 'unread'" 
                            :class="filterTab === 'unread' ? 'bg-white text-emerald-700 shadow-xs font-black' : 'text-slate-500 hover:text-slate-900'"
                            class="flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer flex items-center justify-center gap-1">
                        Unread
                        @if($unreadCount > 0)
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Conversations Scroll Area --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100/70 custom-scrollbar">
                @forelse($conversations as $conv)
                    @php
                        $partner = $conv->partner;
                        $roleName = $partner->roles->first()?->name ?? 'User';
                        $isSelected = $selectedPartner && $selectedPartner->id === $partner->id;
                        $isSentByMe = $conv->last_message->sender_id === auth()->id();
                    @endphp

                    <a href="{{ route('inbox.index', ['user_id' => $partner->id]) }}" 
                       x-show="(filterTab === 'all' || {{ $conv->unread_count > 0 ? 'true' : 'false' }}) && 
                               ('{{ strtolower($partner->name) }}'.includes(searchQuery.toLowerCase()) || 
                                '{{ strtolower($conv->last_message->subject) }}'.includes(searchQuery.toLowerCase()) || 
                                '{{ strtolower(strip_tags($conv->last_message->body)) }}'.includes(searchQuery.toLowerCase()))"
                       class="flex items-start gap-3.5 p-4 transition-all relative group {{ $isSelected ? 'bg-emerald-50/70 border-l-4 border-emerald-500' : 'hover:bg-slate-100/60 bg-transparent' }}">
                        
                        {{-- Avatar with Role Accent --}}
                        <div class="relative shrink-0 mt-0.5">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-sm font-black shadow-xs 
                                @if(str_contains(strtolower($roleName), 'student')) bg-blue-100 text-blue-700 border border-blue-200/60
                                @elseif(str_contains(strtolower($roleName), 'supervisor')) bg-emerald-100 text-emerald-700 border border-emerald-200/60
                                @elseif(str_contains(strtolower($roleName), 'coordinator')) bg-purple-100 text-purple-700 border border-purple-200/60
                                @else bg-slate-800 text-white border border-slate-700 @endif">
                                {{ strtoupper(substr($partner->name, 0, 1)) }}
                            </div>
                            @if($conv->unread_count > 0)
                                <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <h4 class="text-xs font-black text-slate-900 truncate {{ $conv->unread_count > 0 ? 'font-black text-slate-950' : 'font-bold' }}">
                                    {{ $partner->name }}
                                </h4>
                                <span class="text-[10px] font-semibold text-slate-400 shrink-0">
                                    {{ $conv->last_message_at->diffForHumans(null, true, true) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md 
                                    @if(str_contains(strtolower($roleName), 'student')) bg-blue-50 text-blue-600
                                    @elseif(str_contains(strtolower($roleName), 'supervisor')) bg-emerald-50 text-emerald-700
                                    @elseif(str_contains(strtolower($roleName), 'coordinator')) bg-purple-50 text-purple-700
                                    @else bg-slate-100 text-slate-600 @endif">
                                    {{ $roleName }}
                                </span>
                                @if($conv->last_message->subject)
                                    <span class="text-[10px] font-semibold text-slate-500 truncate" title="{{ $conv->last_message->subject }}">
                                        • {{ Str::limit($conv->last_message->subject, 20) }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500 truncate {{ $conv->unread_count > 0 ? 'font-bold text-slate-800' : 'font-medium' }}">
                                @if($isSentByMe)
                                    <span class="text-slate-400 font-semibold">You: </span>
                                @endif
                                {{ Str::limit(strip_tags($conv->last_message->body), 50) }}
                            </p>
                        </div>

                        {{-- Unread Badge --}}
                        @if($conv->unread_count > 0)
                            <div class="shrink-0 self-center">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-black shadow-xs">
                                    {{ $conv->unread_count }}
                                </span>
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="p-8 text-center flex flex-col items-center justify-center h-64">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-slate-700">No conversations yet</p>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-[200px]">Send a message to start communicating with students or staff.</p>
                        <button type="button" 
                                @click="newModalOpen = true" 
                                class="mt-4 px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            Start a Chat
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Right Panel: Active Chat Feed & Composer --}}
        <div class="flex-1 flex flex-col bg-white overflow-hidden relative"
             :class="!mobileShowChat ? 'hidden md:flex' : 'flex'">
            
            @if($selectedPartner)
                @php
                    $partnerRole = $selectedPartner->roles->first()?->name ?? 'User';
                    $lastMessage = $chatMessages->last();
                    $defaultSubject = $lastMessage 
                        ? (str_starts_with($lastMessage->subject, 'Re: ') ? $lastMessage->subject : 'Re: ' . $lastMessage->subject)
                        : 'Direct Message';
                @endphp

                {{-- Chat Header --}}
                <div class="px-6 py-4 border-b border-slate-100 bg-white flex items-center justify-between shrink-0 shadow-xs z-10">
                    <div class="flex items-center gap-3.5">
                        {{-- Mobile Back Button --}}
                        <button type="button" 
                                @click="mobileShowChat = false" 
                                class="md:hidden p-2 -ml-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-sm font-black shadow-xs
                            @if(str_contains(strtolower($partnerRole), 'student')) bg-blue-100 text-blue-700 border border-blue-200/60
                            @elseif(str_contains(strtolower($partnerRole), 'supervisor')) bg-emerald-100 text-emerald-700 border border-emerald-200/60
                            @elseif(str_contains(strtolower($partnerRole), 'coordinator')) bg-purple-100 text-purple-700 border border-purple-200/60
                            @else bg-slate-800 text-white border border-slate-700 @endif">
                            {{ strtoupper(substr($selectedPartner->name, 0, 1)) }}
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-black text-slate-900 leading-tight">{{ $selectedPartner->name }}</h3>
                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md 
                                    @if(str_contains(strtolower($partnerRole), 'student')) bg-blue-50 text-blue-700 border border-blue-200/50
                                    @elseif(str_contains(strtolower($partnerRole), 'supervisor')) bg-emerald-50 text-emerald-700 border border-emerald-200/50
                                    @elseif(str_contains(strtolower($partnerRole), 'coordinator')) bg-purple-50 text-purple-700 border border-purple-200/50
                                    @else bg-slate-100 text-slate-700 border border-slate-200/50 @endif">
                                    {{ $partnerRole }}
                                </span>
                            </div>
                            <p class="text-[11px] font-semibold text-slate-400 mt-0.5 flex items-center gap-2">
                                <span>{{ $selectedPartner->email }}</span>
                                @if($selectedPartner->studentProfile?->program)
                                    <span>• {{ $selectedPartner->studentProfile->program->name }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                onclick="location.reload()" 
                                class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-500 transition-colors border border-slate-200/60" 
                                title="Refresh conversation">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Chat Feed Area (All Previous Messages In Chronological Order) --}}
                <div x-ref="chatContainer" 
                     x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })"
                     class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50 custom-scrollbar">
                    
                    @if($chatMessages->isEmpty())
                        <div class="h-full flex flex-col items-center justify-center text-center p-8">
                            <div class="w-16 h-16 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-emerald-500 mb-4">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-black text-slate-900">Start of conversation</h4>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm">You haven't exchanged messages with {{ $selectedPartner->name }} yet. Write a message below to start chatting!</p>
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
                                <div class="flex items-center justify-center my-4">
                                    <span class="px-3 py-1 rounded-full bg-slate-200/70 text-slate-600 text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                        {{ $msg->created_at->isToday() ? 'Today' : ($msg->created_at->isYesterday() ? 'Yesterday' : $msg->created_at->format('M d, Y')) }}
                                    </span>
                                </div>
                                @php $previousDate = $msgDate; @endphp
                            @endif

                            {{-- Message Bubble --}}
                            <div class="flex items-start gap-3 {{ $isFromMe ? 'justify-end' : 'justify-start' }}">
                                @if(!$isFromMe)
                                    <div class="w-8 h-8 rounded-xl bg-slate-200 flex items-center justify-center text-xs font-black text-slate-700 shrink-0 mt-1">
                                        {{ strtoupper(substr($msg->sender->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="max-w-xl flex flex-col {{ $isFromMe ? 'items-end' : 'items-start' }}">
                                    <div class="rounded-2xl p-4 shadow-xs transition-all 
                                        {{ $isFromMe 
                                            ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-tr-xs' 
                                            : 'bg-white border border-slate-200/80 text-slate-800 rounded-tl-xs' }}">
                                        
                                        {{-- Subject Tag Header --}}
                                        @if($msg->subject)
                                            <div class="text-[10px] font-black uppercase tracking-wider mb-2 flex items-center gap-1.5 
                                                {{ $isFromMe ? 'text-emerald-100/90' : 'text-emerald-600' }}">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                <span>{{ $msg->subject }}</span>
                                            </div>
                                        @endif

                                        {{-- Message Body --}}
                                        <div class="text-xs sm:text-sm leading-relaxed whitespace-pre-line font-medium {{ $isFromMe ? 'text-white' : 'text-slate-800' }}">
                                            {!! nl2br(e($msg->body)) !!}
                                        </div>

                                        {{-- Attachments inside bubble --}}
                                        @if($msg->attachments->count() > 0)
                                            <div class="mt-3 pt-3 space-y-2 border-t {{ $isFromMe ? 'border-white/20' : 'border-slate-100' }}">
                                                @foreach($msg->attachments as $att)
                                                    <a href="{{ route('inbox.attachments.download', $att) }}" 
                                                       class="flex items-center justify-between gap-3 p-2 rounded-xl text-xs font-semibold transition-all 
                                                       {{ $isFromMe ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/60' }}">
                                                        <div class="flex items-center gap-2 truncate">
                                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                            </svg>
                                                            <span class="truncate">{{ $att->file_name }}</span>
                                                        </div>
                                                        <span class="text-[10px] shrink-0 opacity-80">({{ round($att->file_size / 1024, 1) }} KB)</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Time Stamp --}}
                                    <div class="flex items-center gap-1.5 mt-1 px-1 text-[10px] text-slate-400 font-semibold">
                                        <span>{{ $msg->created_at->format('h:i A') }}</span>
                                        @if($isFromMe)
                                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Chat Inline Composer Bar (Pinned Bottom) --}}
                <div class="p-4 bg-white border-t border-slate-100 shrink-0">
                    <form method="POST" 
                          action="{{ route('inbox.store') }}" 
                          enctype="multipart/form-data" 
                          x-ref="chatForm"
                          class="space-y-2">
                        @csrf
                        <input type="hidden" name="to[]" value="{{ $selectedPartner->id }}">
                        <input type="hidden" name="subject" value="{{ $defaultSubject }}">

                        {{-- Attachment Tag Preview --}}
                        <div x-show="chatAttachedFileName" x-cloak class="flex items-center gap-2 p-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="font-bold truncate" x-text="chatAttachedFileName"></span>
                            <button type="button" @click="$refs.chatAttachmentInput.value = ''; chatAttachedFileName = ''" class="ml-auto text-emerald-500 hover:text-emerald-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Input Container --}}
                        <div class="relative flex items-center gap-2 bg-slate-50 hover:bg-slate-100/60 focus-within:bg-white border border-slate-200 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/10 rounded-2xl p-2 transition-all">
                            {{-- Attachment Clip Button --}}
                            <label class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl cursor-pointer transition-colors shrink-0" title="Attach file">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <input type="file" 
                                       name="attachments[]" 
                                       x-ref="chatAttachmentInput" 
                                       @change="chatAttachedFileName = $event.target.files[0] ? $event.target.files[0].name : ''" 
                                       class="hidden" />
                            </label>

                            {{-- Text Message Input --}}
                            <textarea name="body" 
                                      required
                                      rows="1"
                                      placeholder="Write a message to {{ $selectedPartner->name }}... (Press Enter to send)"
                                      class="flex-1 bg-transparent border-none text-xs sm:text-sm font-medium text-slate-900 placeholder-slate-400 focus:ring-0 outline-none resize-none py-2"
                                      @keydown.enter.prevent="if(!$event.shiftKey && $el.value.trim() !== '') { $refs.chatForm.submit(); }"></textarea>

                            {{-- Send Button --}}
                            <button type="submit" 
                                    class="p-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-sm hover:shadow-emerald-500/20 shrink-0 cursor-pointer flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- Empty Selection State --}}
                <div class="h-full flex flex-col items-center justify-center text-center p-8 bg-slate-50/20">
                    <div class="w-20 h-20 rounded-3xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-4 shadow-sm">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-slate-900">Select a Conversation</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs">Choose a contact from the left conversation list or click New Message to start chatting.</p>
                    <button type="button" 
                            @click="newModalOpen = true" 
                            class="mt-5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-2xl transition-all shadow-sm cursor-pointer">
                        Start New Conversation
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- + New Message Modal Dialog --}}
    <div x-show="newModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="newModalOpen = false">
        
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-xl overflow-hidden animate-in-up"
             @click.outside="newModalOpen = false">
            
            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">New Message</h3>
                        <p class="text-[11px] font-semibold text-slate-400">Send a direct message or scholarly inquiry</p>
                    </div>
                </div>

                <button type="button" 
                        @click="newModalOpen = false" 
                        class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Form --}}
            <form method="POST" action="{{ route('inbox.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                
                {{-- Recipient Select --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">To (Recipient)</label>
                    <select name="to[]" 
                            required 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all">
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
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">Subject (Topic)</label>
                    <input type="text" 
                           name="subject" 
                           placeholder="e.g. Seminar Supervisor Request, Milestone Correction..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all" />
                </div>

                {{-- Message Body --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">Message</label>
                    <textarea name="body" 
                              rows="4" 
                              required
                              placeholder="Write your message here..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all resize-none"></textarea>
                </div>

                {{-- Attachment --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">Attachment (Optional, max 10MB)</label>
                    <input type="file" 
                           name="attachments[]" 
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                </div>

                {{-- Modal Actions --}}
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" 
                            @click="newModalOpen = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider transition-all shadow-sm hover:shadow-emerald-500/20">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
