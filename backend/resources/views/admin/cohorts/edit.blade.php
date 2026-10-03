@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-acetel-900 to-acetel-800 p-8 mb-10 shadow-2xl">
        <div class="absolute -right-20 -top-20 opacity-20">
            <svg width="300" height="300" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="#ffffff" d="M39.6,-66.1C52.7,-59.8,65.6,-51.7,73.5,-40.1C81.4,-28.5,84.3,-13.3,83.9,1.7C83.5,16.8,79.8,31.7,71.2,43.4C62.6,55.1,49.1,63.6,35,68.9C20.9,74.2,6.2,76.3,-7.4,74.4C-21,72.5,-33.5,66.6,-45.5,58.8C-57.5,51,-69,41.2,-74.6,28.7C-80.2,16.2,-79.9,1,-76,-13C-72.1,-27.1,-64.6,-40,-53.8,-51.1C-43,-62.2,-28.9,-71.5,-14.2,-74.8C0.5,-78.1,15.2,-75.4,27.1,-70Z" transform="translate(100 100)" />
            </svg>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight">Edit Cohort</h2>
                <p class="mt-2 text-acetel-100 font-medium text-lg max-w-xl">Update details for <span class="font-bold text-white">{{ $cohort->name }}</span>.</p>
            </div>
            <a href="{{ route('admin.cohorts.index') }}" class="group inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-2xl shadow-sm text-sm font-bold text-white transition-all duration-300 backdrop-blur-md">
                <svg class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Directory
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <form action="{{ route('admin.cohorts.update', $cohort) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        
        <div class="bg-white/80 backdrop-blur-xl shadow-xl border border-slate-200/60 rounded-3xl overflow-hidden transition-all duration-500 hover:shadow-2xl">
            <div class="p-8 sm:p-12">
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                    <!-- Left Context Column -->
                    <div class="md:col-span-4 space-y-6">
                        <div class="sticky top-8">
                            <div class="w-12 h-12 bg-acetel-100 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                                <svg class="w-6 h-6 text-acetel-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">Cohort Settings</h3>
                            <p class="mt-3 text-slate-500 leading-relaxed text-sm">
                                Modify the core details of this cohort. Updating the badge code will reflect across the system immediately.
                            </p>
                        </div>
                    </div>

                    <!-- Right Form Column -->
                    <div class="md:col-span-8 space-y-8">
                        <!-- Cohort Name -->
                        <div class="group">
                            <label for="name" class="flex items-center text-sm font-bold text-slate-700 mb-2 transition-colors group-focus-within:text-acetel-600">
                                Cohort Name <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-acetel-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <input type="text" name="name" id="name" class="block w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border-slate-200 text-slate-900 rounded-2xl shadow-sm focus:ring-2 focus:ring-acetel-500/50 focus:border-acetel-500 focus:bg-white transition-all duration-300 sm:text-sm" value="{{ old('name', $cohort->name) }}" placeholder="e.g. 2025/2026 Academic Session" required>
                            </div>
                            @error('name') <p class="text-red-500 text-xs mt-2 font-semibold flex items-center"><svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                            <!-- Cohort Badge (formerly Code) -->
                            <div class="group">
                                <label for="code" class="flex items-center text-sm font-bold text-slate-700 mb-2 transition-colors group-focus-within:text-acetel-600">
                                    Cohort Badge <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-acetel-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                                    </div>
                                    <input type="text" name="code" id="code" class="block w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border-slate-200 text-slate-900 rounded-2xl shadow-sm focus:ring-2 focus:ring-acetel-500/50 focus:border-acetel-500 focus:bg-white transition-all duration-300 sm:text-sm font-mono uppercase tracking-wider" value="{{ old('code', $cohort->code) }}" placeholder="e.g. MSC_AI_2026" required>
                                </div>
                                @error('code') <p class="text-red-500 text-xs mt-2 font-semibold flex items-center"><svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                            </div>

                            <!-- Intake Year -->
                            <div class="group">
                                <label for="intake_year" class="flex items-center text-sm font-bold text-slate-700 mb-2 transition-colors group-focus-within:text-acetel-600">
                                    Intake Year
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-400 group-focus-within:text-acetel-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <input type="number" name="intake_year" id="intake_year" min="2000" max="2100" class="block w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border-slate-200 text-slate-900 rounded-2xl shadow-sm focus:ring-2 focus:ring-acetel-500/50 focus:border-acetel-500 focus:bg-white transition-all duration-300 sm:text-sm" value="{{ old('intake_year', $cohort->intake_year) }}" placeholder="e.g. 2026">
                                </div>
                                @error('intake_year') <p class="text-red-500 text-xs mt-2 font-semibold flex items-center"><svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="group">
                            <label for="status" class="flex items-center text-sm font-bold text-slate-700 mb-2 transition-colors group-focus-within:text-acetel-600">
                                Status <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-acetel-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <select name="status" id="status" class="block w-full pl-11 pr-10 py-3.5 bg-slate-50/50 border-slate-200 text-slate-900 rounded-2xl shadow-sm focus:ring-2 focus:ring-acetel-500/50 focus:border-acetel-500 focus:bg-white transition-all duration-300 sm:text-sm appearance-none cursor-pointer" required>
                                    <option value="active" {{ old('status', $cohort->status) == 'active' ? 'selected' : '' }}>Active - Ready for students</option>
                                    <option value="inactive" {{ old('status', $cohort->status) == 'inactive' ? 'selected' : '' }}>Inactive - Hidden from registration</option>
                                    <option value="archived" {{ old('status', $cohort->status) == 'archived' ? 'selected' : '' }}>Archived - Historical data</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            <div class="mt-3 flex items-start space-x-2 p-3 bg-amber-50/50 rounded-xl border border-amber-100/50">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-xs text-amber-800 font-medium">Archived and Inactive cohorts <span class="font-bold">do not appear</span> for new student registrations.</p>
                            </div>
                            @error('status') <p class="text-red-500 text-xs mt-2 font-semibold flex items-center"><svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 bg-slate-50/80 border-t border-slate-200/60 flex flex-col sm:flex-row justify-between items-center gap-4">
                <button type="button" 
                    data-confirm="CRITICAL ACTION: Are you sure you want to permanently delete this cohort? All linked records will be affected and this action cannot be undone."
                    data-confirm-title="Delete Academic Cohort"
                    data-confirm-type="danger"
                    data-confirm-btn="Delete Cohort"
                    onclick="document.getElementById('delete-cohort-form').submit();"
                    class="group inline-flex items-center justify-center py-2.5 px-5 border-2 border-red-100 text-sm font-bold rounded-xl text-red-600 bg-red-50 hover:bg-red-600 hover:text-white hover:border-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-300 w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Delete Cohort
                </button>

                <button type="submit" class="group relative inline-flex items-center justify-center overflow-hidden rounded-2xl p-4 px-8 font-bold text-white bg-acetel-600 hover:bg-acetel-700 shadow-lg hover:shadow-acetel-500/30 transition-all duration-300 ease-out focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 w-full sm:w-auto">
                    <span class="absolute inset-0 flex h-full w-full justify-center [transform:skew(-12deg)_translateX(-100%)] group-hover:duration-1000 group-hover:[transform:skew(-12deg)_translateX(100%)]">
                        <div class="relative h-full w-8 bg-white/20"></div>
                    </span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
    
    <form id="delete-cohort-form" action="{{ route('admin.cohorts.destroy', $cohort) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
