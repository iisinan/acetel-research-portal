@extends(auth()->user()->hasRole('Admin') ? 'layouts.admin' : (auth()->user()->hasRole('Program Coordinator') ? 'layouts.coordinator' : 'layouts.dashboard'))

@section('header', $presentationTitle)
@section('title', $presentationTitle . ' - ACETEL')

@section('content')
<div class="space-y-8 animate-in-up" x-data="{ activeDate: 'all', search: '' }">

    {{-- Top Hero Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 text-white p-8 sm:p-10 shadow-xl border border-emerald-900/50">
        {{-- Decorative glow shapes --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-3xl">
                @if($todayPresenters->isNotEmpty())
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 border border-red-500/40 text-red-300 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                        <span class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></span>
                        <span>Live Presentation</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Scheduled Presentation</span>
                    </div>
                @endif
                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    {{ $presentationTitle }}
                </h1>
                <p class="text-sm sm:text-base text-slate-300 font-medium leading-relaxed">
                    Official schedule of candidate presentations, panel examination slots, and virtual meeting coordinates for {{ $template->name }}.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-300">
                    @if($startDate && $endDate)
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $startDate }} @if($startDate !== $endDate) – {{ $endDate }} @endif</span>
                        </div>
                    @endif
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                        <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>{{ $allScheduled->count() }} Presenter{{ $allScheduled->count() === 1 ? '' : 's' }} Scheduled</span>
                    </div>
                    @if($todayPresenters->isNotEmpty())
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500 text-slate-950 font-black">
                            <span class="w-2 h-2 rounded-full bg-slate-950 animate-ping"></span>
                            <span>{{ $todayPresenters->count() }} Presenting Today</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row lg:flex-col items-stretch gap-3 shrink-0">
                @if($meetingLink)
                    <a href="{{ $meetingLink }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-2xl font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-500/25 transition-all duration-200 active:scale-95">
                        <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Join Meeting Session</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole('Admin'))
                    <form action="{{ route('admin.milestone-templates.cancel-schedule', $template->id) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit"
                            data-confirm="Are you sure you want to cancel the entire presentation schedule for {{ addslashes($template->name) }}? This will remove all presentation dates, times, and Zoom links for {{ $allScheduled->count() }} scheduled student(s), and remove this presentation tab."
                            data-confirm-title="Cancel Presentation Schedule"
                            data-confirm-type="danger"
                            data-confirm-btn="Cancel Schedule"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-white/10 hover:bg-rose-600/80 text-white rounded-2xl text-xs font-bold transition-all border border-white/15 hover:border-rose-500 shadow-sm active:scale-95">
                            <svg class="w-4 h-4 text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Cancel Schedule</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($allScheduled->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4 text-slate-400">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-base font-black text-slate-800 uppercase tracking-wider">No Active Schedule</h3>
            <p class="text-xs text-slate-500 mt-2 font-medium">All presentations for this milestone have concluded or none have been scheduled yet.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                    Return to Dashboard
                </a>
                @if(auth()->user()->hasRole('Admin'))
                    <a href="{{ route('admin.milestone-templates.index') }}" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition shadow-sm">
                        Milestone Templates
                    </a>
                @endif
            </div>
        </div>
    @else

        {{-- Section 2: On each day of presentation (Requirement 2) --}}
        @if($todayPresenters->isNotEmpty())
            <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-white rounded-3xl p-6 sm:p-8 border-2 border-emerald-500/40 shadow-lg shadow-emerald-500/5 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-emerald-200/60">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/30 shrink-0">
                            <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider mb-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                <span>Today's Live Sessions</span>
                            </div>
                            <h2 class="text-xl font-black text-slate-900 tracking-tight">
                                Presenters for Today — {{ \Carbon\Carbon::parse($todayDateStr)->format('l, F j, Y') }}
                            </h2>
                            <p class="text-xs text-slate-600 font-medium">The following {{ $todayPresenters->count() }} candidate(s) are scheduled to present their work today.</p>
                        </div>
                    </div>

                    @php
                        $todayLink = $todayPresenters->first(fn($m) => !empty($m->meeting_link))?->meeting_link ?? $meetingLink;
                    @endphp
                    @if($todayLink)
                        <a href="{{ $todayLink }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 transition-all active:scale-95 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Join Today's Meeting</span>
                        </a>
                    @endif
                </div>

                {{-- Cards Grid for Today's Presenters --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($todayPresenters as $index => $pres)
                        @php
                            $student = $pres->thesis?->student;
                            $user = $student?->user;
                            $latestSub = $pres->submissions->first();
                            $supervisors = $pres->thesis?->assignments?->map(fn($a) => $a->supervisor?->user?->name)->filter()->implode(', ');
                        @endphp
                        <div class="bg-white rounded-2xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition-shadow space-y-3 flex flex-col justify-between">
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $pres->defence_time ? \Carbon\Carbon::parse($pres->defence_time)->format('g:i A') : 'Slot ' . ($index + 1) }}</span>
                                    </span>
                                    @if($pres->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-100 text-green-700 border border-green-200">
                                            Approved
                                        </span>
                                    @else
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                            Slot #{{ $index + 1 }}
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <h4 class="text-sm font-black text-slate-900 leading-snug">
                                        {{ $user?->name ?? 'Candidate' }}
                                    </h4>
                                    <p class="text-xs text-slate-500 font-semibold">{{ $student?->student_id_number ?? 'N/A' }} &bull; {{ $student?->program?->name ?? 'Program' }}</p>
                                </div>

                                @if($pres->thesis && $pres->thesis->title)
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 font-medium line-clamp-2" title="{{ $pres->thesis->title }}">
                                        <span class="font-bold text-slate-900">Topic:</span> {{ $pres->thesis->title }}
                                    </div>
                                @endif

                                @if(!empty($supervisors))
                                    <p class="text-[11px] text-slate-500 font-medium">
                                        <span class="font-bold text-slate-700">Supervisor(s):</span> {{ $supervisors }}
                                    </p>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                                @if($latestSub && $latestSub->file_url)
                                    <button type="button"
                                        @click.prevent="$dispatch('open-document-preview', {
                                            url: '{{ Storage::url($latestSub->file_url) }}',
                                            title: '{{ addslashes($user?->name ?? 'Student') }} - {{ addslashes($template->name) }}',
                                            type: '{{ str_ends_with(strtolower($latestSub->file_url), '.pdf') ? 'pdf' : 'other' }}'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Manuscript</span>
                                    </button>
                                @else
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">No Doc</span>
                                @endif

                                <div class="flex items-center gap-1.5 ml-auto">
                                    @if($pres->meeting_link || $meetingLink)
                                        <a href="{{ $pres->meeting_link ?: $meetingLink }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition-colors">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Join Room</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Section 1: Before / All Scheduled Presenters (Requirement 1) --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>All Scheduled Presenters</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-slate-100 text-slate-700">
                            {{ $allScheduled->count() }} Total
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Browse candidates by date slot or use the search bar to locate specific presenters.</p>
                </div>

                {{-- Search Bar --}}
                <div class="relative w-full md:w-80">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Search presenter, matric, topic..."
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-sm">
                </div>
            </div>

            {{-- Date Tabs --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
                <button type="button" @click="activeDate = 'all'"
                        :class="activeDate === 'all' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0">
                    All Dates ({{ $allScheduled->count() }})
                </button>

                @foreach($groupedByDate as $dateKey => $items)
                    @php
                        $isDateToday = ($dateKey === $todayDateStr);
                    @endphp
                    <button type="button" @click="activeDate = '{{ $dateKey }}'"
                            :class="activeDate === '{{ $dateKey }}' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-1.5">
                        @if($isDateToday)
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        @endif
                        <span>{{ \Carbon\Carbon::parse($dateKey)->format('D, M d') }}</span>
                        <span class="text-[10px] opacity-75">({{ $items->count() }})</span>
                        @if($isDateToday)
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-emerald-700 text-white">Today</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Comprehensive Presenters Table --}}
            <div class="overflow-hidden border border-slate-100 rounded-2xl shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3.5 w-12 text-center">#</th>
                                <th class="px-4 py-3.5">Presenter Details</th>
                                <th class="px-4 py-3.5">Research Topic</th>
                                <th class="px-4 py-3.5">Date & Time</th>
                                <th class="px-4 py-3.5">Supervisors</th>
                                <th class="px-4 py-3.5">Manuscript</th>
                                <th class="px-4 py-3.5 text-center">Meeting Link</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($allScheduled as $index => $sm)
                                @php
                                    $student = $sm->thesis?->student;
                                    $user = $student?->user;
                                    $studentName = $user?->name ?? 'Candidate';
                                    $matricNo = $student?->student_id_number ?? 'N/A';
                                    $topic = $sm->thesis?->title ?? 'Topic Pending';
                                    $supervisors = $sm->thesis?->assignments?->map(fn($a) => $a->supervisor?->user?->name)->filter()->implode(', ');
                                    $dateStr = \Carbon\Carbon::parse($sm->defence_date)->format('Y-m-d');
                                    $isToday = ($dateStr === $todayDateStr);
                                    $latestSub = $sm->submissions->first();
                                    $rowLink = $sm->meeting_link ?: $meetingLink;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors {{ $isToday ? 'bg-emerald-50/30' : '' }}"
                                    x-show="(activeDate === 'all' || activeDate === '{{ $dateStr }}') && (!search || '{{ strtolower(addslashes($studentName)) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($matricNo)) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($topic)) }}'.includes(search.toLowerCase()))">
                                    <td class="px-4 py-3.5 text-center font-bold text-slate-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-emerald-600/10 text-emerald-700 font-black flex items-center justify-center text-xs shrink-0">
                                                {{ strtoupper(substr($studentName, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    <span>{{ $studentName }}</span>
                                                    @if($isToday)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">Today</span>
                                                    @endif
                                                    @if($sm->status === 'approved')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-green-100 text-green-800">Approved</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-medium">
                                                    {{ $matricNo }} &bull; {{ $student?->program?->name ?? 'Program' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 max-w-xs">
                                        <p class="font-medium text-slate-800 line-clamp-2" title="{{ $topic }}">
                                            {{ $topic }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($sm->defence_date)->format('M d, Y') }}</span>
                                        </div>
                                        @if($sm->defence_time)
                                            <div class="text-[11px] text-emerald-700 font-semibold mt-0.5 flex items-center gap-1 pl-5">
                                                <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>{{ \Carbon\Carbon::parse($sm->defence_time)->format('g:i A') }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 font-medium">
                                        {{ $supervisors ?: 'Unassigned' }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($latestSub && $latestSub->file_url)
                                            <button type="button" 
                                                @click.prevent="$dispatch('open-document-preview', { 
                                                    url: '{{ Storage::url($latestSub->file_url) }}', 
                                                    title: '{{ addslashes($studentName) }} - {{ addslashes($template->name) }}',
                                                    type: '{{ str_ends_with(strtolower($latestSub->file_url), '.pdf') ? 'pdf' : 'other' }}'
                                                })"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                                <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>View Doc</span>
                                            </button>
                                        @else
                                            <span class="text-slate-400 font-semibold text-[11px]">Not uploaded</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @php
                                            $tableEventType = $template->defence_type ?? 'seminar';
                                            $tableDefEvent = $sm->thesis?->defenceEvents?->where('type', $tableEventType)->first();
                                            $tableCanEval = $tableDefEvent && $tableDefEvent->isAuthorizedEvaluator(auth()->id());
                                            $tableEval = $tableCanEval ? $tableDefEvent->evaluations->firstWhere('evaluator_id', auth()->id()) : null;
                                        @endphp
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            @if($tableCanEval && $isToday)
                                                @if($tableEval && $tableEval->submitted_at)
                                                    <a href="{{ route('evaluations.show', $tableEval->id) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors">
                                                        <span>Evaluated</span>
                                                    </a>
                                                @else
                                                    <a href="{{ route('evaluations.create', $tableDefEvent->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition-colors shadow-sm">
                                                        <span>Score</span>
                                                    </a>
                                                @endif
                                            @endif

                                            @if($rowLink)
                                                <a href="{{ $rowLink }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition-colors">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>Zoom Link</span>
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-[11px]">-</span>
                                            @endif

                                            @if(auth()->user()->hasRole('Admin'))
                                                <a href="{{ route('admin.milestone-templates.index') }}" title="Manage Milestones" class="p-1 text-slate-400 hover:text-emerald-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif

</div>
@endsection
