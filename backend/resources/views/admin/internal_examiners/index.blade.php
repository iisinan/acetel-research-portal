@extends('layouts.admin')

@section('content')
<div class="md:flex md:items-center md:justify-between mb-8">
    <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-extrabold leading-7 text-black sm:text-3xl sm:truncate">
            Internal Examiners
        </h2>
        <p class="mt-1 text-sm text-black">
            Manage staff who can review student progress and final work.
        </p>
    </div>
    <div class="mt-4 flex md:mt-0 md:ml-4">
        <a href="{{ route('admin.internal-examiners.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-acetel-600 hover:bg-acetel-700 focus:outline-none transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add Examiner
        </a>
    </div>
</div>

<div class="bg-white shadow-sm border border-slate-200 overflow-hidden rounded-2xl">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-black uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-black uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-black uppercase tracking-wider">Department/Program</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-black uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-black uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse($examiners as $examiner)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-100 flex items-center justify-center text-black font-bold border border-slate-200">
                                {{ substr($examiner->user->name, 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-black">{{ $examiner->user->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-black font-medium">{{ $examiner->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-black">{{ $examiner->program->name ?? 'All Programs' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($examiner->active && $examiner->user->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.internal-examiners.edit', $examiner) }}" class="text-acetel-600 hover:text-acetel-900 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <form action="{{ route('admin.internal-examiners.destroy', $examiner) }}" method="POST"
                                data-confirm="Are you sure you want to remove this examiner? This will revoke their access to review student submissions."
                                data-confirm-title="Remove Internal Examiner"
                                data-confirm-type="danger"
                                data-confirm-btn="Remove Examiner">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-black hover:text-red-600 transition-colors" title="Remove Examiner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-black italic">No internal examiners found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($examiners->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">
        {{ $examiners->links() }}
    </div>
    @endif
</div>
@endsection
