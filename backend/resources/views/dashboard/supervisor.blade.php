@extends('layouts.dashboard')

@section('header')
    Faculty Oversight



<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="milestone_id" id="global-jump-target">
</form>

@push('scripts')
<script>
    function jumpMilestoneGlobal(studentId, targetMilestoneId) {
        if (!confirm('Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.')) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("students") }}/' + studentId + '/set-milestone-global';
        document.getElementById('global-jump-target').value = targetMilestoneId;
        form.submit();
    }
</script>
@endpush
@endsection

@section('content')
<div class="space-y-8 animate-in">
    {{-- Welcome Hero --}}
    <div class="relative overflow-hidden rounded-3xl bg-white border border-green-100 shadow-sm p-8 lg:p-10">
        <div class="absolute top-0 right-0 w-72 h-72 bg-green-50 rounded-full -mr-20 -mt-20 opacity-60"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-green-50 rounded-full -ml-16 -mb-16 opacity-40"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-8">
            {{-- Left: Greeting --}}
            <div class="flex-1 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-full border border-green-200">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-600"></span>
                    </span>
                    <span class="text-xs font-semibold text-green-700 uppercase tracking-widest text-[10px]">Mentorship Active</span>
                </div>

                <div>
                    <h2 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tight leading-tight">
                        Welcome back, <span class="text-green-600">{{ auth()->user()->firstName() }}</span>
                    </h2>
                    <p class="mt-2 text-slate-500 font-medium leading-relaxed max-w-xl">
                        Monitor the scholarly trajectories of your assigned candidates. Review submissons, authorize milestones, and manage academic mentorship.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 pt-1">
                    <a href="{{ route('inbox.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all duration-200 group">
                        Institutional Inbox
                        @if($stats['unread_inbox'] > 0)
                            <span class="bg-white text-green-700 text-[10px] font-black rounded-full w-4 h-4 flex items-center justify-center">{{ $stats['unread_inbox'] }}</span>
                        @endif
                    </a>

                    @if($stats['unread_chat'] > 0)
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all duration-200 group relative">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            Candidate Feedback
                            <span class="bg-white text-blue-700 text-[10px] font-black rounded-full w-4 h-4 flex items-center justify-center animate-bounce">{{ $stats['unread_chat'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Load Summary --}}
            <div class="shrink-0 flex flex-col items-center justify-center bg-green-50 border border-green-100 rounded-2xl p-8 min-w-[170px] text-center">
                <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-2">Supervision Load</p>
                <p class="text-5xl font-black text-slate-800 tracking-tighter leading-none">
                    {{ $stats['assigned_students'] }}<span class="text-lg text-slate-400 font-bold">/{{ $supervisor->max_students ?? 10 }}</span>
                </p>
                <div class="w-full mt-4 bg-green-100 rounded-full h-2 overflow-hidden">
                    @php $percentLoad = min(100, ($stats['assigned_students'] / ($supervisor->max_students ?? 10)) * 100); @endphp
                    <div class="h-full bg-green-600 rounded-full transition-all duration-1000" style="width: {{ $percentLoad }}%"></div>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-2">Managed Roster</p>
            </div>
         </div>
    </div>

    {{-- Alerts System for Supervisor --}}
    @if(isset($pending_reviews) && $pending_reviews->count() > 0)
    <div class="relative rounded-[2.5rem] bg-amber-50 border border-amber-200 shadow-sm transition-all duration-500 overflow-hidden" 
         x-data="supervisorDashboard()">
        <div class="absolute inset-0 opacity-40 mix-blend-multiply pointer-events-none rounded-[2.5rem]" style="background-image: url(\"data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23f59e0b' fill-opacity='0.1' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='1'/%3E%3C/g%3E%3C/svg%3E\");"></div>
        
        <!-- Header / Toggle -->
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 p-8 cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-5 w-full md:w-auto">
                <div class="w-14 h-14 rounded-2xl bg-white border border-amber-200 flex items-center justify-center shrink-0 shadow-sm text-amber-500">
                    <span class="relative flex h-4 w-4">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                    </span>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight leading-none mb-1">Attention Required</h3>
                    <p class="text-[11px] font-bold text-slate-500">{{ $pending_reviews->count() }} candidate {{ Str::plural('milestone', $pending_reviews->count()) }} require your immediate mentorship review.</p>
                </div>
            </div>
            
            <div class="flex-shrink-0 w-full md:w-auto">
                 <button type="button" 
                    @click.stop="expanded = !expanded"
                    class="w-full md:w-auto px-8 py-4 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black tracking-widest uppercase text-[10px] transition-all shadow-lg shadow-amber-500/30 flex items-center justify-center gap-3 outline-none focus:outline-none">
                    <span x-text="expanded ? 'Hide Candidates' : 'Review Candidates'"></span>
                    <svg class="w-4 h-4 opacity-70 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
            </div>
        </div>

        <!-- Expanded Students Container -->
        <div x-show="expanded" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="px-8 pb-8 pt-0 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($pending_reviews as $review)
                    <a href="{{ route('theses.show', ['thesis' => $review->thesis_project_id, 'expanded' => $review->id]) }}#milestone-{{$review->id}}" class="block bg-white border border-amber-100 rounded-[1.5rem] p-5 hover:border-amber-400 hover:shadow-lg transition-all group relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-bl from-amber-100 to-transparent opacity-50 rounded-bl-[4rem]"></div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-[1rem] bg-amber-50 text-amber-600 flex items-center justify-center font-black text-lg border border-amber-100 group-hover:bg-amber-500 group-hover:text-white transition-all shadow-sm shrink-0">
                                {{ substr($review->thesis->student->user->name ?? '?', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0 pt-0.5">
                                <h4 class="text-sm font-black text-slate-800 truncate mb-1">{{ $review->thesis->student->user->name ?? 'Candidate' }}</h4>
                                <span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200/50 rounded-md text-[9px] font-bold uppercase tracking-widest truncate max-w-full">
                                    {{ $review->template->name }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-5 pt-4 border-t border-slate-50 flex items-center justify-between relative z-10">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $review->submitted_at ? $review->submitted_at->diffForHumans() : 'Recently' }}</span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('theses.show', ['thesis' => $review->thesis_project_id, 'expanded' => $review->id]) }}#milestone-{{$review->id}}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                                    Review
                                </a>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Metrics Matrix --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $sCards = [
                ['label' => 'Total Students', 'value' => $stats['assigned_students'], 'color' => 'green', 'icon' => 'user-group'],
                ['label' => 'Pending Reviews', 'value' => $stats['pending_reviews'], 'color' => 'amber', 'icon' => 'clipboard-check'],
                ['label' => 'Active Theses', 'value' => $stats['total_theses'], 'color' => 'blue', 'icon' => 'academic-cap'],
                ['label' => 'Inbox Messages', 'value' => $stats['unread_inbox'], 'color' => 'emerald', 'icon' => 'inbox'],
                ['label' => 'Chat Feedback', 'value' => $stats['unread_chat'], 'color' => 'blue', 'icon' => 'chat-alt-2'],
            ];
            $iconMap = [
                'user-group' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'clipboard-check' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'academic-cap' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222',
                'chat-alt-2' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
                'inbox' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
            ];
            $colorMap = [
                'green' => 'bg-green-50 border-green-100 text-green-600',
                'amber' => 'bg-amber-50 border-amber-100 text-amber-600',
                'blue' => 'bg-blue-50 border-blue-100 text-blue-600',
                'emerald' => 'bg-emerald-50 border-emerald-100 text-emerald-600',
            ];
        @endphp
        @foreach($sCards as $sc)
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-xl border flex items-center justify-center {{ $colorMap[$sc['color']] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconMap[$sc['icon']] }}"/></svg>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest leading-none">{{ $sc['label'] }}</p>
            </div>
            <p class="text-3xl font-black text-slate-800 tracking-tight">{{ $sc['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Managed Students Roster --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-6 bg-green-500 rounded-full"></div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight">MANAGED STUDENTS</h3>
                    </div>
                </div>

                <div class="divide-y divide-slate-50">
                    @forelse($students as $student)
                        <div class="p-8 hover:bg-slate-50/50 transition-colors group">
                             <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                                 <div class="flex items-center gap-5">
                                     <div class="w-14 h-14 rounded-2xl bg-green-50 border border-green-100 flex items-center justify-center text-green-700 font-black text-xl shadow-sm group-hover:bg-green-600 group-hover:text-white transition-all duration-300">
                                         {{ substr($student->user->name, 0, 1) }}
                                     </div>
                                     <div class="min-w-0">
                                         <h4 class="text-lg font-black text-slate-800 group-hover:text-green-600 transition-colors tracking-tight leading-none mb-2 uppercase">{{ $student->user->name }}</h4>
                                         <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">{{ $student->program->code }} • {{ $student->student_id_number }}</p>
                                         <div class="flex items-center gap-3">
                                            <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-green-500 rounded-full" style="width: {{ $student->overall_progress }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-black text-green-600">{{ $student->overall_progress }}% Progress</span>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="flex items-center gap-3">
                                     <a href="{{ route('inbox.compose', ['reply_to' => $student->user_id]) }}" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-green-600 hover:border-green-200 transition-all shadow-sm" title="Send Message">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                     </a>
                                     <div x-data="{ jumpMenuOpen: false }" class="relative inline-block text-left">
                                         <button @click.prevent.stop="jumpMenuOpen = !jumpMenuOpen" class="flex items-center justify-center p-3 bg-white border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
                                             <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                         </button>
                                         <div x-show="jumpMenuOpen" @click.outside="jumpMenuOpen = false" x-cloak class="absolute right-0 top-12 w-56 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden text-left" style="display: none;">
                                             <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                 Jump Milestone
                                             </div>
                                             <div class="max-h-48 overflow-y-auto p-1">
                                                 @foreach(\App\Models\MilestoneTemplate::orderBy('order')->get() as $t)
                                                     @php
                                                        $sm = $student->thesis ? $student->thesis->milestones->where('milestone_template_id', $t->id)->first() : null;
                                                     @endphp
                                                     @if($sm)
                                                     <button type="button" onclick="jumpMilestoneGlobal('{{ $student->id }}', '{{ $sm->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition-colors flex items-center justify-between group">
                                                         <span>{{ $t->name }}</span>
                                                         @if($student->thesis && $student->thesis->currentMilestone && $t->id === $student->thesis->currentMilestone->milestone_template_id)
                                                             <svg class="w-3 h-3 text-green-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                         @endif
                                                     </button>
                                                     @endif
                                                 @endforeach
                                             </div>
                                         </div>
                                     </div>
                                     <a href="{{ route('theses.show', $student->thesis) }}" class="flex items-center justify-center px-6 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-xs font-black uppercase tracking-widest hover:bg-green-600 hover:border-green-600 hover:text-white transition-all shadow-sm">
                                         Audit Thesis
                                     </a>
                                 </div>
                             </div>
                        </div>
                    @empty
                        <div class="py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">No Managed Candidates Detected</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending Actions Sidebar --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Pending Reviews --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-800 tracking-widest">PENDING CLEARANCES</h3>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-black rounded-full border border-amber-100">{{ $pending_reviews->count() }}</span>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($pending_reviews->take(5) as $milestone)
                        <div class="p-4 bg-amber-50/50 border border-amber-100 rounded-2xl group hover:bg-amber-50 transition-all">
                             <p class="text-xs font-black text-slate-800 mb-1 truncate">{{ $milestone->template->name }}</p>
                             <div class="flex items-center justify-between gap-2">
                                 <span class="text-[10px] font-bold text-slate-500 truncate">{{ $milestone->thesis->student->user->name }}</span>
                                 <a href="{{ route('theses.show', ['thesis' => $milestone->thesis_project_id, 'expanded' => $milestone->id]) }}" class="text-[9px] font-black text-amber-600 uppercase tracking-widest flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                     Review <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                 </a>
                             </div>
                        </div>
                    @empty
                        <div class="py-10 text-center bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Audit Backlog Clear</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pending Evaluations --}}
            @if($pending_evaluations->count() > 0)
            <div class="bg-white rounded-3xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-blue-50 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-800 tracking-widest">VIVA EVALUATIONS</h3>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-black rounded-full border border-blue-100">{{ $pending_evaluations->count() }}</span>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($pending_evaluations->take(3) as $event)
                        <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-2xl">
                             <p class="text-xs font-black text-slate-800 mb-1">{{ $event->defence_type }} Defence</p>
                             <p class="text-[10px] font-bold text-slate-500 mb-3">{{ $event->thesis->student->user->name }}</p>
                             <a href="{{ route('evaluations.create', ['defenceEvent' => $event->id]) }}" class="w-full flex items-center justify-center py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-colors">
                                 Grade Now
                             </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Institutional Resource Center --}}
            @if(isset($document_templates) && $document_templates->count() > 0)
            <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-800 tracking-widest uppercase">Institutional Resources</h3>
                </div>
                <div class="p-2 space-y-1">
                    @foreach($document_templates as $template)
                    <a href="{{ route('templates.download', $template) }}" class="flex items-center gap-3 p-3 hover:bg-green-50 rounded-2xl transition-all group">
                        <div class="w-8 h-8 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0 group-hover:bg-green-600 group-hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-black text-slate-700 truncate leading-none uppercase">{{ $template->title }}</p>
                            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">v{{ $template->version }} • {{ strtoupper($template->type) }}</p>
                        </div>
                        <svg class="w-3 h-3 ml-auto text-slate-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Protocol --}}
            <div class="bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-500 rounded-full -mr-16 -mt-16 opacity-10 group-hover:scale-110 transition-transform duration-700"></div>
                <h3 class="text-xl font-black italic tracking-tighter leading-tight mb-4 uppercase">Clearance <br/><span class="text-green-500 uppercase">Protocol</span></h3>
                <p class="text-xs font-medium leading-relaxed opacity-60 italic mb-8">Authorize doctoral candidate trajectory completions and certify divisional peer reviews.</p>
                <div class="space-y-3">
                    <a href="{{ route('repository.index') }}" class="w-full flex items-center justify-center py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">Research Archive</a>
                    <a href="{{ route('profile.edit') }}" class="w-full flex items-center justify-center py-4 bg-green-600 hover:bg-green-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">System Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('supervisorDashboard', () => ({
        expanded: false,
        async approveMilestone(event, milestoneId) {
            if (!confirm('Are you sure you want to approve this candidate milestone?')) return;
            
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            try {
                const response = await fetch(`/milestones/${milestoneId}/quick-approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        type: 'clear_role',
                        role: '{{ Auth::user()->getRoleNames()->first() }}'
                    })
                });
                const data = await response.json();
                if (data.success) {
                    window.toast.success(data.message);
                    const card = btn.closest('.block');
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => card.remove(), 500);
                    }
                } else {
                    window.toast.error(data.message);
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                window.toast.error('Oversight error occurred.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    }));
});
</script>
@endpush



<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="milestone_id" id="global-jump-target">
</form>

@push('scripts')
<script>
    function jumpMilestoneGlobal(studentId, targetMilestoneId) {
        if (!confirm('Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.')) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("students") }}/' + studentId + '/set-milestone-global';
        document.getElementById('global-jump-target').value = targetMilestoneId;
        form.submit();
    }
</script>
@endpush
@endsection
