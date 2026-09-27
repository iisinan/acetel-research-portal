@extends('layouts.dashboard')

@section('header', 'Assigned Theses')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Assigned Theses for Examination</h2>
    </div>

    @if($theses->isEmpty())
        <div class="bg-white rounded-2xl p-8 border border-slate-200 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">No Theses Assigned</h3>
            <p class="text-slate-500 max-w-md mx-auto">You have not been assigned to examine any theses yet.</p>
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">Student</th>
                            <th class="px-6 py-4">Thesis Title</th>
                            <th class="px-6 py-4">Program</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($theses as $thesis)
                            @php
                                $roleLabel = '';
                                $roleClass = '';
                                if (in_array($thesis->internal_examiner_profile_id, $internalProfileIds)) {
                                    $roleLabel = 'Internal Examiner';
                                    $roleClass = 'bg-blue-50 text-blue-700';
                                } elseif (in_array($thesis->external_examiner_profile_id, $externalProfileIds)) {
                                    $roleLabel = 'External Examiner';
                                    $roleClass = 'bg-purple-50 text-purple-700';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $thesis->studentProfile->user->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-slate-500">{{ $thesis->studentProfile->student_id_number ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-800 line-clamp-2 max-w-xs" title="{{ $thesis->title }}">
                                        {{ $thesis->title ?? 'Untitled Thesis' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $thesis->program->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $roleClass }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('examiner.theses.show', $thesis->id) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($theses->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $theses->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
