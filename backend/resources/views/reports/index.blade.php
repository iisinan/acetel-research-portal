@extends('layouts.dashboard')

@section('header')
    Reports & Analytics
@endsection

@section('content')
<div class="space-y-8 animate-in-up">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white border border-gray-100 rounded-3xl p-5 sm:p-8 shadow-sm">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Institutional Reports Archives</h1>
            <p class="text-xs sm:text-sm font-medium text-gray-500 mt-1.5 sm:mt-2">Export real-time institutional data securely to CSV for your meetings and external record keeping.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-8">
        <div class="flex items-center gap-3 sm:gap-4 mb-5 sm:mb-6">
            <div class="w-1 h-8 bg-brand-500 rounded-full"></div>
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Thesis Status Report</h3>
                <p class="text-xs sm:text-sm font-medium text-gray-500 mt-1">Export a list of students, their current thesis status, assigned supervisors, and last activity date.</p>
            </div>
        </div>
        
        <form action="{{ route('reports.export') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 sm:gap-6 items-end bg-gray-50/50 p-4 sm:p-6 rounded-2xl border border-gray-100">
            <input type="hidden" name="type" value="thesis_status">
            
            <!-- Program Filter -->
            <div class="md:col-span-2 space-y-2">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Academic Program</label>
                <select name="program_id" class="w-full bg-white border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:ring-brand-500 focus:border-brand-500 px-4 py-3 transition-colors shadow-sm">
                    <option value="">All Programs</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->code }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Cohort Filter -->
            <div class="md:col-span-2 space-y-2">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Intake Cohort</label>
                <select name="cohort_id" class="w-full bg-white border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:ring-brand-500 focus:border-brand-500 px-4 py-3 transition-colors shadow-sm">
                    <option value="">All Cohorts</option>
                    @foreach($cohorts as $cohort)
                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-1">
                <button type="submit" class="w-full py-3 bg-brand-600 text-white rounded-xl font-semibold text-sm hover:bg-brand-700 transition-colors shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export CSV
                </button>
            </div>
        </form>
    </div>

    <!-- Future Reports Modules -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-gray-50 rounded-3xl border border-gray-200 border-dashed p-8 flex flex-col items-center justify-center text-center opacity-60 min-h-[250px] relative overflow-hidden group">
            <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <h4 class="text-lg font-bold text-gray-800">Milestone Analytics Report</h4>
            <p class="text-sm font-medium text-gray-500 mt-2">Comprehensive completion trends and aggregated velocity statistics.</p>
            <div class="mt-4 px-3 py-1 bg-gray-200 rounded-md text-xs font-bold text-gray-600 uppercase tracking-widest">In Development</div>
        </div>

        <div class="bg-gray-50 rounded-3xl border border-gray-200 border-dashed p-8 flex flex-col items-center justify-center text-center opacity-60 min-h-[250px] relative overflow-hidden group">
            <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h4 class="text-lg font-bold text-gray-800">Faculty Capacity Index</h4>
            <p class="text-sm font-medium text-gray-500 mt-2">Deep dive into institutional advising load and supervisory bottlenecks.</p>
            <div class="mt-4 px-3 py-1 bg-gray-200 rounded-md text-xs font-bold text-gray-600 uppercase tracking-widest">In Development</div>
        </div>
    </div>
</div>
@endsection
