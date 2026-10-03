@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-black tracking-tight">System Status</h1>
            <p class="mt-2 text-black font-medium">Monitor tasks, storage, and platform health.</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
             <form action="{{ route('admin.operations.flush') }}" method="POST"
                 data-confirm="WARNING: This will delete ALL failed background jobs from the queue. This action cannot be undone."
                 data-confirm-title="Clear Failed Queue Tasks"
                 data-confirm-type="danger"
                 data-confirm-btn="Clear All Failed Tasks">
                 @csrf
                 <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none transition-colors">
                     <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                     Clear Failed Tasks
                 </button>
             </form>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-200 relative group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-amber-50 rounded-lg p-3 group-hover:scale-110 transition-transform">
                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-semibold text-black truncate">Pending Tasks</dt>
                            <dd class="flex items-baseline">
                                <span class="text-2xl font-bold text-black">{{ $pendingJobs }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-200 relative group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-50 rounded-lg p-3 group-hover:scale-110 transition-transform">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-semibold text-black truncate">Failed Tasks</dt>
                            <dd class="flex items-baseline">
                                <span class="text-2xl font-bold {{ $failedJobsCount > 0 ? 'text-red-600' : 'text-black' }}">{{ $failedJobsCount }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-200 relative group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-acetel-50 rounded-lg p-3 group-hover:scale-110 transition-transform">
                        <svg class="h-6 w-6 text-acetel-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-semibold text-black truncate">Storage Disk</dt>
                            <dd class="flex items-baseline">
                                <span class="text-xl font-bold text-black uppercase">{{ $storageDisk }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Failed Jobs List -->
    <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-black">Recent Failures</h3>
            @if($failedJobsCount > 0)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                    Action Required
                </span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Queue</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Exception</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider">Failed At</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-black uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($failedJobs as $job)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black">
                                {{ $job->queue }}
                            </td>
                            <td class="px-6 py-4 text-sm text-black max-w-lg truncate" title="{{ $job->exception }}">
                                {{ Str::limit(explode("\n", $job->exception)[0], 80) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('admin.operations.retry', $job->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-acetel-600 hover:text-acetel-900 font-bold bg-acetel-50 px-3 py-1.5 rounded-lg transition-colors">Retry</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-500 mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <h3 class="text-sm font-medium text-black">All Clear</h3>
                                <p class="mt-1 text-sm text-black">No failed background jobs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
