@extends('layouts.admin')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-6">
    <div class="sm:flex-auto">
        <h1 class="text-2xl font-extrabold text-black tracking-tight">Cohorts</h1>
        <p class="mt-2 text-sm text-black font-medium">Manage academic intakes, sessions, and manage student enrollments.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex space-x-3">
        <a href="{{ route('admin.cohorts.export-all-emails') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Export All Emails
        </a>
        <a href="{{ route('admin.cohorts.create') }}" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-acetel-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-acetel-700 focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create Cohort
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
    <form action="{{ route('admin.cohorts.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5 items-end">
        <div>
            <label for="search" class="block text-sm font-semibold text-black">Search</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or Code..." class="mt-1 block w-full rounded-xl border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-acetel-500 focus:ring-acetel-500 sm:text-sm">
        </div>

        <div>
            <label for="status" class="block text-sm font-semibold text-black">Status</label>
            <select name="status" id="status" class="mt-1 block w-full rounded-xl border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-acetel-500 focus:ring-acetel-500 sm:text-sm">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full inline-flex justify-center items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-black shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 transition-colors">
                <svg class="h-4 w-4 mr-2 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter Results
            </button>
        </div>
    </form>
</div>

<div class="flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm ring-1 ring-slate-200 rounded-2xl">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-black uppercase tracking-wider">Cohort Details</th>

                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-black uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-black uppercase tracking-wider">Students</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-black uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($cohorts as $cohort)
                        <tr class="hover:bg-slate-50 transition-colors duration-150">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-black">{{ $cohort->name }}</span>
                                    <span class="text-xs font-medium text-black">{{ $cohort->code }}</span>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($cohort->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">Active</span>
                                @elseif($cohort->status === 'inactive')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">Inactive</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-black border border-slate-200">Archived</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-black">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-acetel-50 text-acetel-700 border border-acetel-200">
                                    {{ $cohort->students_count }} Enrolled
                                </span>
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('admin.cohorts.show', $cohort) }}" class="text-black hover:text-black font-semibold transition-colors" title="View Details">
                                        View
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('admin.cohorts.export-emails', $cohort) }}" class="text-blue-600 hover:text-blue-900 font-semibold transition-colors" title="Export Emails">
                                        Export
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('admin.cohorts.register-students', $cohort) }}" class="text-acetel-600 hover:text-acetel-900 font-semibold transition-colors" title="Enroll Students">
                                         Register
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('admin.cohorts.edit', $cohort) }}" class="text-slate-500 hover:text-slate-900 font-semibold transition-colors" title="Edit Cohort">
                                         Edit
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <form action="{{ route('admin.cohorts.toggle-status', $cohort) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="{{ $cohort->status === 'active' ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} font-semibold transition-colors" title="Toggle Status">
                                            {{ $cohort->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v16m8-8H4" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-black">No cohorts found</h3>
                                <p class="mt-1 text-sm text-black">Get started by creating a new academic cohort.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.cohorts.create') }}" class="inline-flex items-center rounded-xl bg-acetel-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-acetel-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-acetel-600">
                                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                        </svg>
                                        Create Cohort
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="mt-6">
    {{ $cohorts->links() }}
</div>
@endsection
