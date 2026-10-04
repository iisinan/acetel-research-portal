@extends('layouts.admin')

@section('header')
    Seminar Examinations
@endsection

@section('content')
    <div class="py-12" x-data="{
        selectedMilestones: [],
        selectAll: false,
        toggleAll() {
            if (this.selectAll) {
                this.selectedMilestones = {{ json_encode($milestones->pluck('id')) }};
            } else {
                this.selectedMilestones = [];
            }
        }
    }">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Bulk Schedule Seminar Presentations</h3>
                <div class="mb-4">
                    <a href="{{ route('seminars.attendance') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                        Download Examiner Attendance
                    </a>
                </div>
                <form action="{{ route('seminars.schedule') }}" method="POST" class="flex gap-4 items-end">
                    @csrf
                    <template x-for="id in selectedMilestones">
                        <input type="hidden" name="milestone_ids[]" :value="id">
                    </template>
                    
                    <div class="w-1/3">
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="w-1/3">
                        <label class="block text-sm font-medium text-gray-700">Students Per Day</label>
                        <input type="number" name="students_per_day" value="5" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50" :disabled="selectedMilestones.length === 0">
                            Generate Schedule
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" x-model="selectAll" @change="toggleAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Examiner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PPT</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Grade</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($milestones as $milestone)
                                @php
                                    $event = $milestone->thesis->defenceEvents->first();
                                    $examiners = $event ? $event->panelMembers->where('role', 'Examiner') : collect();
                                    $examinerIds = $examiners->pluck('user_id')->toArray();
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="'{{ $milestone->id }}'" x-model="selectedMilestones" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $milestone->thesis->student->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $milestone->thesis->student->matric_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->format('M d, Y') : 'Not scheduled' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($examiners->count() > 0)
                                            @foreach($examiners as $ex)
                                                <div class="text-xs">{{ $ex->user->name }}</div>
                                            @endforeach
                                        @else
                                            <span class="text-red-500">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($milestone->submissions->count() > 0)
                                            <a href="{{ Storage::url($milestone->submissions->first()->file_url) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Download</a>
                                        @else
                                            <span class="text-gray-400">Not uploaded</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @php
                                            $evaluations = $event ? $event->evaluations : collect();
                                            $avg = $evaluations->count() > 0 ? $evaluations->avg(function($e) { return $e->score["total"] ?? 0; }) : null;
                                        @endphp
                                        @if($avg !== null)
                                            <span class="font-bold text-green-600">{{ number_format($avg, 1) }} / 100</span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex flex-col gap-2">
                                            <form action="{{ route('seminars.assign-examiner', $milestone->id) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                            <select name="supervisor_profile_ids[]" multiple required class="block w-full py-1 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" style="height: 80px;">
                                                @foreach($supervisors as $sup)
                                                    <option value="{{ $sup->id }}" {{ in_array($sup->user_id, $examinerIds ?? []) ? 'selected' : '' }}>
                                                        {{ $sup->user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-xs font-bold">Assign</button>
                                            </form>
                                            @if($event)
                                                <form action="{{ route('seminars.score', $event->id) }}" method="POST" class="flex items-center gap-2">
                                                    @csrf
                                                    @php
                                                        $myEval = $event->evaluations->where('evaluator_id', auth()->id())->first();
                                                    @endphp
                                                    <input type="number" name="score" value="{{ $myEval ? ($myEval->score['total'] ?? '') : '' }}" min="0" max="100" placeholder="Grade (0-100)" required class="block w-24 py-1 text-sm border-gray-300 rounded-md">
                                                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs font-bold">{{ $myEval ? 'Update Grade' : 'Grade' }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $milestones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
