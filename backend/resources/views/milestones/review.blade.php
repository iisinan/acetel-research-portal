@extends('layouts.dashboard')

@section('header')
    Review Milestone
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-black dark:text-white">Review: {{ $milestone->template->name }}</h1>
            <p class="mt-1 text-black dark:text-black">
                Student: <span class="font-medium text-black dark:text-white">{{ $milestone->thesis->student->user->name }}</span> | 
                Program: {{ $milestone->thesis->student->program->code }}
            </p>
        </div>
        <div id="milestone-status-badge" class="flex items-center gap-3">
             <x-badge :type="match($milestone->status) {
                    'not_started' => 'default',
                    'in_progress' => 'info',
                    'submitted' => 'warning',
                    'revision_required' => 'danger',
                    'approved' => 'success',
                    default => 'default'
                }">
                <span id="milestone-status-text">{{ ucfirst(str_replace('_', ' ', $milestone->status)) }}</span>
            </x-badge>
            
            @if($milestone->template->allow_defence_date)
                <a href="{{ route('meeting.join', $milestone) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-brand-400 text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-slate-800 transition-colors shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Join Virtual Room
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Submission Details -->
        <div>
            <x-card title="Submission Status">
                @if($submission)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-black dark:text-black">Submitted on {{ $submission->created_at->format('M d, Y at H:i') }}</span>
                            <span class="text-xs font-mono bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded">v{{ $submission->version }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-3 mb-4">
                            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>
                            <div>
                                <h4 class="text-sm font-medium text-black dark:text-white">{{ $submission->file_meta['original_name'] ?? 'document.pdf' }}</h4>
                                <p class="text-xs text-black dark:text-black">{{ number_format(($submission->file_meta['size'] ?? 0) / 1024, 2) }} KB</p>
                            </div>
                            <a href="{{ Storage::url($submission->file_url) }}" target="_blank" class="ml-auto bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 px-3 py-1 rounded text-sm font-medium text-black dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                Download
                            </a>
                        </div>
                        
                        @if($submission->description)
                            <div class="text-sm text-black dark:text-gray-300 bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-700">
                                <strong>Student Note:</strong> {{ $submission->description }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8 text-black">
                        No submission uploaded yet.
                    </div>
                @endif
            </x-card>
            
            @if($milestone->submissions->count() > 1)
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-black dark:text-white mb-3">Previous Versions</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        @foreach($milestone->submissions->where('id', '!=', $submission?->id)->sortByDesc('created_at') as $oldSub)
                             <li class="px-4 py-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="text-sm">
                                    <span class="font-medium text-black dark:text-white">v{{ $oldSub->version }}</span>
                                    <span class="text-black dark:text-black ml-2">{{ $oldSub->created_at->format('M d') }}</span>
                                </div>
                                <a href="{{ Storage::url($oldSub->file_url) }}" class="text-acetel-600 dark:text-acetel-400 text-sm hover:underline">View</a>
                             </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Review Form -->
        <div>
            @if(Auth::user()->can('review', $milestone))
                <x-card title="Evaluation">
                    <form action="{{ route('milestones.review.update', $milestone) }}" method="POST" class="space-y-6" id="milestone-review-form" onsubmit="submitEvaluation(event)">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Decision</label>
                            <div class="flex space-x-4">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/10 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-green-900/20 w-full transition">
                                    <input type="radio" name="decision" value="approved" class="h-4 w-4 text-green-600 focus:ring-green-500" required>
                                    <span class="ml-3 font-medium text-black dark:text-white">Approve</span>
                                </label>
                                
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/10 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20 w-full transition">
                                    <input type="radio" name="decision" value="rejected" class="h-4 w-4 text-red-600 focus:ring-red-500">
                                    <span class="ml-3 font-medium text-black dark:text-white">Request Revisions</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Remarks / Feedback</label>
                            <textarea name="remarks" rows="6" class="shadow-sm focus:ring-acetel-500 focus:border-acetel-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" placeholder="Enter detailed feedback for the student..." required></textarea>
                        </div>

                        @if(Auth::user()->hasRole('Program Coordinator') && $milestone->template->show_supervisor_assignment)
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/10 rounded-lg border border-purple-100 dark:border-purple-900/30">
                            <h4 class="text-sm font-bold text-purple-900 dark:text-purple-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                Assign Supervisors
                            </h4>
                            <div class="space-y-3">
                                <p class="text-xs text-black italic mb-2">
                                    Requirements: {{ str_contains(strtoupper($milestone->thesis->student->level->name), 'MSC') ? 'Exactly 2 supervisors' : 'Exactly 3 supervisors' }}
                                </p>
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($availableSupervisors as $supervisor)
                                    <label class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border border-transparent has-[:checked]:border-purple-500 transition">
                                        <input type="checkbox" name="supervisor_ids[]" value="{{ $supervisor->id }}" class="h-4 w-4 text-purple-600 focus:ring-purple-500 rounded" {{ $milestone->thesis->assignments->contains('supervisor_profile_id', $supervisor->id) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-black dark:text-gray-300">{{ $supervisor->user->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        @php
                            $canSetDefenceDate = $milestone->template->allow_defence_date && (Auth::user()->hasRole('Admin') || Auth::user()->hasRole($milestone->template->defence_date_role ?? 'Program Coordinator'));
                        @endphp

                        @if($canSetDefenceDate)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-acetel-50 dark:bg-acetel-900/10 rounded-lg border border-acetel-100 dark:border-acetel-900/30">
                            <div class="md:col-span-2">
                                 <h4 class="text-sm font-bold text-acetel-900 dark:text-acetel-300 mb-2 flex items-center">
                                     <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                     Schedule Institutional Defence
                                 </h4>
                            </div>
                            <div>
                                <label for="defence_date" class="block text-xs font-bold text-black dark:text-gray-300 uppercase tracking-wider mb-1">Defence Date</label>
                                <input type="date" name="defence_date" id="defence_date" value="{{ $milestone->defence_date ? $milestone->defence_date->format('Y-m-d') : '' }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-acetel-500 focus:ring-acetel-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="defence_location" class="block text-xs font-bold text-black dark:text-gray-300 uppercase tracking-wider mb-1">Location / Venue</label>
                                <input type="text" name="defence_location" id="defence_location" value="{{ $milestone->defence_location }}" placeholder="e.g. Conference Room A or Zoom Link" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-acetel-500 focus:ring-acetel-500 sm:text-sm">
                            </div>
                        </div>
                        @endif

                        @if(Auth::user()->hasRole('Program Coordinator') && $milestone->template->show_internal_examiner_assignment)
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/10 rounded-lg border border-amber-100 dark:border-amber-900/30 mt-4">
                            <label for="internal_examiner_profile_id" class="block text-xs font-bold text-black dark:text-gray-300 uppercase tracking-wider mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Designate Internal Examiner
                            </label>
                            <select name="internal_examiner_profile_id" id="internal_examiner_profile_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-acetel-500 focus:ring-acetel-500 sm:text-sm" required>
                                <option value="">Select Internal Examiner...</option>
                                @foreach($internalExaminers as $examiner)
                                    <option value="{{ $examiner->id }}" {{ $milestone->thesis->internal_examiner_profile_id == $examiner->id ? 'selected' : '' }}>
                                        {{ $examiner->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[10px] text-amber-700 italic">This examiner will be formally linked to the candidate's examination panel.</p>
                        </div>
                        @endif

                        @if(Auth::user()->hasRole('Program Coordinator') && $milestone->template->show_external_examiner_assignment)
                        <div class="p-4 bg-rose-50 dark:bg-rose-900/10 rounded-lg border border-rose-100 dark:border-rose-900/30 mt-4">
                            <label for="external_examiner_profile_id" class="block text-xs font-bold text-black dark:text-gray-300 uppercase tracking-wider mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Designate External Examiner
                            </label>
                            <select name="external_examiner_profile_id" id="external_examiner_profile_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-acetel-500 focus:ring-acetel-500 sm:text-sm" required>
                                <option value="">Select External Examiner...</option>
                                @foreach($externalExaminers as $examiner)
                                    <option value="{{ $examiner->id }}" {{ $milestone->thesis->external_examiner_profile_id == $examiner->id ? 'selected' : '' }}>
                                        {{ $examiner->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[10px] text-rose-700 italic">This examiner will evaluate the final Viva Voce.</p>
                        </div>
                        @endif

                        @if($milestone->template->order == 3)
                        <div class="p-4 bg-acetel-50 dark:bg-acetel-900/10 rounded-lg border border-acetel-100 dark:border-acetel-900/30 text-sm text-acetel-800 dark:text-acetel-300">
                            <p class="font-bold mb-1">Communication Assessment</p>
                            <p>As Program Coordinator, please verify that regular communication is happening between the student and supervisors through the official channels.</p>
                        </div>
                        @endif

                        <!-- Action Items (Dynamic To-Do List) -->
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-700" x-data="{ items: [] }">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-widest flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                    Protocol Action Items
                                </h4>
                                <button type="button" @click="items.push({ content: '', due_date: '' })" class="text-[10px] font-black text-primary-600 uppercase tracking-widest hover:text-primary-700 transition-colors">
                                    + Add Task
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                @if($pendingActionItems->count() > 0)
                                <div class="mb-4">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Pending Verification</p>
                                    <div class="space-y-2">
                                        @foreach($pendingActionItems as $pItem)
                                        <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm transition-all hover:shadow-md">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full {{ $pItem->status === 'completed' ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-300' }}"></div>
                                                <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">{{ $pItem->content }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-[9px] font-black uppercase {{ $pItem->status === 'completed' ? 'text-emerald-600' : 'text-slate-400' }}">{{ $pItem->status }}</span>
                                                @if($pItem->status === 'completed')
                                                    <form action="{{ route('action-items.verify', $pItem) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="p-1.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Verify Correction">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <template x-for="(item, index) in items" :key="index">
                                    <div class="flex flex-col sm:flex-row gap-3 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm relative group">
                                        <div class="flex-1">
                                            <input type="text" :name="`action_items[${index}][content]`" x-model="item.content" placeholder="Specific correction required..." class="w-full text-xs font-medium border-none focus:ring-0 bg-transparent p-0 placeholder-slate-400" required>
                                        </div>
                                        <div class="sm:border-l border-slate-100 dark:border-slate-800 sm:pl-3 flex items-center">
                                            <input type="date" :name="`action_items[${index}][due_date]`" x-model="item.due_date" class="text-[10px] font-black text-slate-500 uppercase tracking-tighter border-none focus:ring-0 bg-transparent p-0 w-[110px]">
                                        </div>
                                        <button type="button" @click="items.splice(index, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </template>
                                
                                <div x-show="items.length === 0 && {{ $pendingActionItems->count() }} === 0" class="py-4 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest italic">No specific action items defined.</p>
                                </div>
                            </div>
                            <p class="mt-4 text-[9px] text-slate-400 font-medium leading-relaxed italic">Action items will be tracked in the student's dashboard as a mandatory verification pipeline.</p>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-acetel-600 hover:bg-acetel-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-acetel-500">
                                Submit Evaluation
                            </button>
                        </div>
                    </form>
                </x-card>
            @else
                <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4 border border-amber-200">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <h3 class="text-lg font-black text-amber-900 dark:text-amber-200 uppercase tracking-tight">Passive Monitoring Mode</h3>
                    <p class="mt-2 text-sm text-amber-800 dark:text-amber-400 max-w-sm mx-auto leading-relaxed">
                        @php
                            $workflowService = app(\App\Services\MilestoneWorkflowService::class);
                            $reason = $workflowService->getApprovalBlockReason($milestone, Auth::user());
                        @endphp
                        {{ $reason ?? 'You do not have active review credentials for this specific milestone stage at this time.' }}
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-xs font-black text-amber-900 dark:text-amber-300 uppercase tracking-widest hover:underline">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- WhatsApp-Style Institutional Comm-Link -->
    <div class="mt-8">
        <x-comm-link :messages="$milestone->messages" 
                    :thesisId="$milestone->thesis_project_id" 
                    :milestoneId="$milestone->id" 
                    title="Audit Discussion" />
    </div>
</div>
@push('scripts')
<script>
async function submitEvaluation(event) {
    event.preventDefault();
    const form = event.target;
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerText;
    
    btn.disabled = true;
    btn.innerText = 'Transmitting Evaluation...';
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.toast.success(data.message);
            
            // Update the status badge
            const badgeText = document.getElementById('milestone-status-text');
            if (badgeText) badgeText.innerText = data.status_label;
            
            // Update badge color (simple way)
            const badge = document.getElementById('milestone-status-badge').querySelector('span');
            if (badge && data.status === 'approved') {
                badge.className = badge.className.replace(/bg-\w+-100/g, 'bg-green-100').replace(/text-\w+-800/g, 'text-green-800');
            }
            
            btn.innerText = 'Evaluation Recorded';
            btn.classList.remove('bg-acetel-600');
            btn.classList.add('bg-emerald-600');
            
            // Optional: redirect after some delay if it was a full approval
            if (data.status === 'approved') {
                setTimeout(() => {
                    window.location.href = '{{ route("dashboard") }}';
                }, 1500);
            }
        } else {
            window.toast.error(data.message || 'Evaluation submission failed.');
            btn.disabled = false;
            btn.innerText = originalText;
        }
    } catch (error) {
        console.error('Fetch error:', error);
        window.toast.error('Connecton error during transmission.');
        btn.disabled = false;
        btn.innerText = originalText;
    }
}
</script>
@endpush
@endsection
