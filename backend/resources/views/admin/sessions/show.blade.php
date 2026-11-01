@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sessions.index') }}" class="text-black hover:text-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-black">{{ $session->name }}</h1>
            </div>
            <p class="text-black mt-1 ml-9">{{ $session->start_date->format('M Y') }} - {{ $session->end_date->format('M Y') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.create', ['cohort_id' => $session->id, 'role' => 'Student']) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-black shadow-sm hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Student
            </a>
            <a href="{{ route('admin.users.import-form', ['cohort_id' => $session->id]) }}" class="inline-flex items-center px-4 py-2 bg-acetel-600 border border-transparent rounded-md font-semibold text-white shadow-sm hover:bg-acetel-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Upload CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-xs font-medium text-black mb-1">Search Students</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Matric No..." class="w-full pl-10 rounded-md border-gray-300 focus:border-acetel-500 focus:ring-acetel-200">
                </div>
            </div>
            <div class="w-full md:w-64">
                <label class="block text-xs font-medium text-black mb-1">Program</label>
                <select name="program_filter" class="w-full rounded-md border-gray-300 focus:border-acetel-500 focus:ring-acetel-200">
                    <option value="">All Programs</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ request('program_filter') == $prog->id ? 'selected' : '' }}>{{ $prog->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-md hover:bg-slate-700">Filter</button>
            </div>
        </form>
    </div>

    <!-- Student List -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Matric No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Program / Level</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-black uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-acetel-100 flex items-center justify-center text-acetel-600 font-bold text-xs">
                                {{ substr($student->user->name, 0, 2) }}
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-black">{{ $student->user->name }}</div>
                                <div class="text-xs text-black">{{ $student->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-black">
                        {{ $student->student_id_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-black">{{ $student->program->code ?? '-' }}</div>
                        <div class="text-xs text-black">{{ $student->level->name ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $student->enrollment_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-black' }}">
                            {{ ucfirst($student->enrollment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.users.edit', $student->user) }}" class="text-acetel-600 hover:text-acetel-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-black">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="text-lg font-medium text-black">No students found</span>
                            <span class="text-sm text-black mt-1">Try adjusting your filters or add a new student.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($students->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $students->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
