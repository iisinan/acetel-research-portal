@extends('layouts.admin')

@section('header')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Past Seminar Results</h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Archived records of completed presentations, attendance, and scores.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.past-presentations.export-scores') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition-all shadow-sm">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export All Scores
        </a>
        <a href="{{ route('admin.past-presentations.export-attendance') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition-all shadow-sm">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Export Attendance
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-bold tracking-wider">
                <tr>
                    <th class="px-6 py-4">Student</th>
                    <th class="px-6 py-4">Matric Number</th>
                    <th class="px-6 py-4">Seminar</th>
                    <th class="px-6 py-4">Defence Date</th>
                    <th class="px-6 py-4">Avg Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($milestones as $sm)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-900">
                            {{ $sm->thesis->student->user->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $sm->thesis->student->student_id_number ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $sm->template->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">
                            @php
                                $avgScore = 'N/A';
                                $event = $sm->thesis->defenceEvents->where('type', $sm->template->defence_type ?? 'first_seminar')->first();
                                if ($event && $event->evaluations->count() > 0) {
                                    $total = 0;
                                    $count = 0;
                                    foreach($event->evaluations as $eval) {
                                        if (isset($eval->score['total'])) {
                                            $total += $eval->score['total'];
                                            $count++;
                                        }
                                    }
                                    if ($count > 0) {
                                        $avgScore = round($total / $count, 1) . ' / 100';
                                    }
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg {{ $avgScore === 'N/A' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $avgScore }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            No past presentations found. When you "End Session", the records will appear here.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($milestones->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $milestones->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('auto_download_scores'))
            setTimeout(function() {
                window.open("{{ session('auto_download_scores') }}", "_self");
            }, 500);
        @endif
        
        @if(session('auto_download_attendance'))
            setTimeout(function() {
                let iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = "{{ session('auto_download_attendance') }}";
                document.body.appendChild(iframe);
            }, 1500);
        @endif
    });
</script>
@endpush

