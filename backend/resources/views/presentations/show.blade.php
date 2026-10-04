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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($todayPresenters as $index => $pres)
                        @php
                            $student = $pres->thesis?->student;
                            $user = $student?->user;
                            $studentName = $user?->name ?? 'Candidate';
                            $matricNo = $student?->student_id_number ?? 'N/A';
                            $latestSub = $pres->submissions->first();
                            $todayEventType = $template->defence_type ?? 'first_seminar';
                            $todayDefEvent = $pres->thesis?->defenceEvents?->where('type', $todayEventType)->first();
                            $todayExaminers = $todayDefEvent ? $todayDefEvent->panelMembers->map(fn($pm) => $pm->user?->name)->filter()->implode(', ') : '';
                            $todaySupervisors = $pres->thesis?->assignments?->map(fn($a) => $a->supervisor?->user?->name)->filter()->implode(', ');
                            $todayDisplayPanel = $todayExaminers ?: $todaySupervisors;
                            $todayCanEval = $todayDefEvent && $todayDefEvent->isAuthorizedEvaluator(auth()->id());
                            $todayEval = $todayCanEval ? $todayDefEvent->evaluations->firstWhere('evaluator_id', auth()->id()) : null;
                            $todayCardLink = $pres->meeting_link ?: $meetingLink;
                        @endphp
                        <div class="bg-white rounded-3xl p-6 border-2 border-emerald-200/90 shadow-md shadow-emerald-500/5 hover:shadow-xl hover:border-emerald-400 transition-all duration-300 space-y-4 flex flex-col justify-between group">
                            <div class="space-y-3.5">
                                {{-- Slot & Status Row --}}
                                <div class="flex items-center justify-between gap-2 pb-3 border-b border-emerald-100/70">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-black border border-emerald-200/80 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $pres->defence_time ? \Carbon\Carbon::parse($pres->defence_time)->format('g:i A') : 'Slot ' . ($index + 1) }}</span>
                                    </span>
                                    @if($pres->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-300/80">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                            Slot #{{ $index + 1 }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Presenter Info --}}
                                <div class="flex items-start gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white font-black text-sm flex items-center justify-center shadow-md shadow-emerald-500/20 ring-2 ring-emerald-500/10 shrink-0">
                                        {{ strtoupper(substr($studentName, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-black text-slate-900 group-hover:text-emerald-700 transition-colors leading-snug truncate">
                                            {{ $studentName }}
                                        </h4>
                                        <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-500 font-medium">
                                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-mono font-bold text-[10px] border border-slate-200/60 shrink-0">
                                                {{ $matricNo }}
                                            </span>
                                            <span class="text-slate-300">•</span>
                                            <span class="truncate" title="{{ $student?->program?->name ?? 'Program' }}">
                                                {{ $student?->program?->name ?? 'Program' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Topic --}}
                                @if($pres->thesis)
                                    @if(Auth::user()->hasRole('Student') && Auth::user()->studentProfile?->id === $pres->thesis->student_profile_id)
                                        <form action="{{ route('theses.update', $pres->thesis) }}" method="POST" class="mt-2 flex items-center gap-2 w-full">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="title" value="{{ $pres->thesis->title }}" class="flex-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-slate-800" required placeholder="Enter thesis title...">
                                            <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-800 transition-colors whitespace-nowrap shadow-2xs">Save</button>
                                        </form>
                                    @elseif($pres->thesis->title)
                                        <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-xs text-slate-800 font-bold leading-snug line-clamp-2" title="{{ $pres->thesis->title }}">
                                            {{ $pres->thesis->title }}
                                        </div>
                                    @endif
                                @endif

                                {{-- Panel / Supervisors --}}
                                <div class="space-y-1 pt-1">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Panel / Supervisors</div>
                                    <div class="flex flex-wrap gap-1">
                                        @if($todayDefEvent && $todayDefEvent->panelMembers->count() > 0)
                                            @foreach($todayDefEvent->panelMembers as $pm)
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-purple-50 border border-purple-200/70 text-purple-800 text-[10px] font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                    <span class="truncate max-w-[150px]">{{ $pm->user?->name ?? 'Panel Member' }}</span>
                                                </span>
                                            @endforeach
                                        @elseif($pres->thesis?->assignments?->count() > 0)
                                            @foreach($pres->thesis->assignments as $assign)
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50 border border-blue-200/70 text-blue-800 text-[10px] font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                    <span class="truncate max-w-[150px]">{{ $assign->supervisor?->user?->name ?? 'Supervisor' }}</span>
                                                </span>
                                            @endforeach
                                        @elseif(!empty($todayDisplayPanel))
                                            @foreach(explode(',', $todayDisplayPanel) as $p)
                                                @if(trim($p))
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-emerald-50 border border-emerald-200/70 text-emerald-800 text-[10px] font-bold">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        <span class="truncate max-w-[150px]">{{ trim($p) }}</span>
                                                    </span>
                                                @endif
                                            @endforeach
                                        @else
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">Unassigned</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions Footer --}}
                            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                                @if($latestSub && $latestSub->file_url)
                                    <button type="button"
                                        @click.prevent="$dispatch('open-document-preview', {
                                            url: '{{ Storage::url($latestSub->file_url) }}',
                                            title: '{{ addslashes($studentName) }} - {{ addslashes($template->name) }}',
                                            type: '{{ str_ends_with(strtolower($latestSub->file_url), '.pdf') ? 'pdf' : 'other' }}'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-900 text-slate-700 hover:text-white border border-slate-200/80 hover:border-slate-900 text-xs font-bold transition-all shadow-2xs group/btn active:scale-95">
                                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover/btn:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>View Doc</span>
                                    </button>
                                @else
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">No Doc</span>
                                @endif

                                <div class="flex items-center gap-2 ml-auto">
                                    @if($todayCanEval)
                                        @if($todayEval && $todayEval->submitted_at)
                                            <a href="{{ route('evaluations.show', $todayEval->id) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-purple-100 hover:bg-purple-200 text-purple-800 border border-purple-200 transition-all shadow-2xs active:scale-95">
                                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <span>Graded</span>
                                            </a>
                                        @else
                                            <a href="{{ route('evaluations.create', $todayDefEvent->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-purple-600 hover:bg-purple-700 text-white shadow-sm shadow-purple-600/25 transition-all active:scale-95 hover:-translate-y-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>Score</span>
                                            </a>
                                        @endif
                                    @endif

                                    @if($todayCardLink)
                                        <a href="{{ $todayCardLink }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200/80 hover:border-blue-600 shadow-2xs transition-all active:scale-95 group/zoom">
                                            <svg class="w-3.5 h-3.5 text-blue-600 group-hover/zoom:text-white shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50/90 border-b border-slate-200/70 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-4 w-14 text-center">#</th>
                                <th class="px-5 py-4 min-w-[240px]">Presenter Details</th>
                                <th class="px-5 py-4 min-w-[260px] max-w-sm">Research Topic</th>
                                <th class="px-5 py-4 min-w-[170px]">Date & Time</th>
                                <th class="px-5 py-4 min-w-[220px]">Panel / Supervisors</th>
                                <th class="px-5 py-4 min-w-[140px] text-center">Manuscript</th>
                                <th class="px-5 py-4 min-w-[190px] text-center">Actions</th>
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
                                    $tableEventType = $template->defence_type ?? 'first_seminar';
                                    $tableDefEvent = $sm->thesis?->defenceEvents?->where('type', $tableEventType)->first();
                                    $examiners = $tableDefEvent ? $tableDefEvent->panelMembers->map(fn($pm) => $pm->user?->name)->filter()->implode(', ') : '';
                                    $supervisors = $sm->thesis?->assignments?->map(fn($a) => $a->supervisor?->user?->name)->filter()->implode(', ');
                                    $displayPanel = $examiners ?: $supervisors;
                                    $dateStr = \Carbon\Carbon::parse($sm->defence_date)->format('Y-m-d');
                                    $isToday = ($dateStr === $todayDateStr);
                                    $latestSub = $sm->submissions->first();
                                    $rowLink = $sm->meeting_link ?: $meetingLink;
                                    $tableCanEval = $tableDefEvent && $tableDefEvent->isAuthorizedEvaluator(auth()->id());
                                    $tableEval = $tableCanEval ? $tableDefEvent->evaluations->firstWhere('evaluator_id', auth()->id()) : null;
                                @endphp
                                <tr class="group hover:bg-slate-50/70 transition-all duration-150 {{ $isToday ? 'bg-emerald-50/25 border-l-4 border-l-emerald-500' : '' }}"
                                    x-show="(activeDate === 'all' || activeDate === '{{ $dateStr }}') && (!search || '{{ strtolower(addslashes($studentName)) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($matricNo)) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($topic)) }}'.includes(search.toLowerCase()))">
                                    <td class="px-4 py-4 text-center align-middle">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-slate-100 text-slate-500 font-black text-xs border border-slate-200/60 group-hover:bg-slate-200/80 group-hover:text-slate-700 transition-colors">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 align-middle">
                                        <div class="flex items-center gap-3.5">
                                            <div class="relative shrink-0">
                                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white font-black text-sm flex items-center justify-center shadow-sm shadow-emerald-600/20 ring-2 ring-emerald-500/10">
                                                    {{ strtoupper(substr($studentName, 0, 1)) }}
                                                </div>
                                                @if($isToday)
                                                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 border-2 border-white"></span>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-black text-slate-900 text-sm tracking-tight leading-snug group-hover:text-emerald-700 transition-colors">
                                                        {{ $studentName }}
                                                    </span>
                                                    @if($isToday)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300/80 shadow-2xs">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                                            Today
                                                        </span>
                                                    @endif
                                                    @if($sm->status === 'approved')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-300/80">
                                                            Approved
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-500 font-medium">
                                                    <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-mono font-bold text-[10px] border border-slate-200/60 shrink-0">
                                                        {{ $matricNo }}
                                                    </span>
                                                    <span class="text-slate-300">•</span>
                                                    <span class="truncate" title="{{ $student?->program?->name ?? 'Program' }}">
                                                        {{ $student?->program?->name ?? 'Program' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-middle">
                                        @if(Auth::user()->hasRole('Student') && Auth::user()->studentProfile?->id === $sm->thesis?->student_profile_id)
                                            <form action="{{ route('theses.update', $sm->thesis) }}" method="POST" class="flex flex-col gap-1.5 w-full">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="title" value="{{ $sm->thesis->title }}" class="w-full px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all text-slate-800" required placeholder="Enter thesis title...">
                                                <button type="submit" class="self-start px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition-colors shadow-2xs">Update Title</button>
                                            </form>
                                        @else
                                            <div class="p-3 rounded-2xl bg-slate-50/70 border border-slate-100/90 group-hover:border-slate-200 group-hover:bg-white transition-all">
                                                <p class="font-bold text-slate-800 text-xs leading-relaxed line-clamp-2" title="{{ $topic }}">
                                                    {{ $topic }}
                                                </p>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 align-middle whitespace-nowrap">
                                        <div class="inline-flex flex-col gap-1.5">
                                            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200/70 shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span>{{ \Carbon\Carbon::parse($sm->defence_date)->format('M d, Y') }}</span>
                                            </div>
                                            @if($sm->defence_time)
                                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-black border border-emerald-200/80 shadow-2xs">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span>{{ \Carbon\Carbon::parse($sm->defence_time)->format('g:i A') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-middle">
                                        <div class="flex flex-col gap-1.5 max-w-xs">
                                            @if($tableDefEvent && $tableDefEvent->panelMembers->count() > 0)
                                                @foreach($tableDefEvent->panelMembers as $pm)
                                                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-slate-50/90 border border-slate-200/70 text-slate-800 text-[11px] font-bold">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 shrink-0"></span>
                                                        <span class="truncate">{{ $pm->user?->name ?? 'Panel Member' }}</span>
                                                        @if($pm->role)
                                                            <span class="ml-auto text-[9px] uppercase px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded font-black shrink-0">{{ $pm->role }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @elseif($sm->thesis?->assignments?->count() > 0)
                                                @foreach($sm->thesis->assignments as $assign)
                                                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-slate-50/90 border border-slate-200/70 text-slate-800 text-[11px] font-bold">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
                                                        <span class="truncate">{{ $assign->supervisor?->user?->name ?? 'Supervisor' }}</span>
                                                        @if($assign->role)
                                                            <span class="ml-auto text-[9px] uppercase px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded font-black shrink-0">{{ $assign->role }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @elseif(!empty($displayPanel))
                                                @foreach(explode(',', $displayPanel) as $person)
                                                    @if(trim($person))
                                                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-slate-50/90 border border-slate-200/70 text-slate-800 text-[11px] font-bold">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                            <span class="truncate">{{ trim($person) }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">Unassigned</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-middle text-center whitespace-nowrap">
                                        @if($latestSub && $latestSub->file_url)
                                            <button type="button" 
                                                @click.prevent="$dispatch('open-document-preview', { 
                                                    url: '{{ Storage::url($latestSub->file_url) }}', 
                                                    title: '{{ addslashes($studentName) }} - {{ addslashes($template->name) }}',
                                                    type: '{{ str_ends_with(strtolower($latestSub->file_url), '.pdf') ? 'pdf' : 'other' }}'
                                                })"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-900 text-slate-700 hover:text-white border border-slate-200/80 hover:border-slate-900 text-xs font-bold transition-all shadow-2xs group/btn active:scale-95">
                                                <svg class="w-3.5 h-3.5 text-slate-500 group-hover/btn:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>View Doc</span>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-200/60 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                                <svg class="w-3 h-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>No Doc</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 align-middle text-center whitespace-nowrap">
                                        <div class="inline-flex items-center justify-center gap-2">
                                            @if($tableCanEval)
                                                @if($tableEval && $tableEval->submitted_at)
                                                    <a href="{{ route('evaluations.show', $tableEval->id) }}" 
                                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-purple-100 hover:bg-purple-200 text-purple-800 border border-purple-200 transition-all shadow-2xs active:scale-95">
                                                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Graded</span>
                                                    </a>
                                                @else
                                                    @if($isToday)
                                                        <a href="{{ route('evaluations.create', $tableDefEvent->id) }}" 
                                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-purple-600 hover:bg-purple-700 text-white shadow-sm shadow-purple-600/25 transition-all active:scale-95 hover:-translate-y-0.5">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            <span>Score</span>
                                                        </a>
                                                    @else
                                                        <span title="Scoring opens on the presentation date" 
                                                              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-400 border border-slate-200/70 cursor-not-allowed">
                                                            <span>Score</span>
                                                        </span>
                                                    @endif
                                                @endif
                                            @endif

                                            @if($rowLink)
                                                <a href="{{ $rowLink }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200/80 hover:border-blue-600 shadow-2xs transition-all active:scale-95 group/zoom">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 group-hover/zoom:text-white shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>Zoom Link</span>
                                                </a>
                                            @else
                                                <span class="text-slate-300 text-xs">-</span>
                                            @endif

                                            @if(auth()->user()->hasRole('Admin'))
                                                <a href="{{ route('admin.milestone-templates.index') }}" title="Manage Milestones" 
                                                   class="p-1.5 rounded-xl text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 border border-slate-200/70 hover:border-emerald-200 transition-all shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
