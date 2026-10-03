@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-black sm:text-3xl sm:truncate">Edit Announcement</h2>
            <p class="mt-2 text-sm text-black">Update the details of this announcement.</p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-xl shadow-sm text-sm font-bold text-black hover:bg-slate-50 focus:outline-none transition-colors">
                <svg class="w-4 h-4 mr-2 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Directory
            </a>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-slate-200 overflow-hidden rounded-2xl">
        <div class="px-4 py-5 sm:p-8">
            <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                        <label for="title" class="block text-sm font-semibold text-black mb-1">Title</label>
                        <input type="text" name="title" id="title" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ old('title', $announcement->title) }}" required>
                        @error('title') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                        <label for="type" class="block text-sm font-semibold text-black mb-1">Type</label>
                        <select id="type" name="type" class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                            <option value="info" {{ $announcement->type == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="warning" {{ $announcement->type == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="danger" {{ $announcement->type == 'danger' ? 'selected' : '' }}>Danger</option>
                            <option value="success" {{ $announcement->type == 'success' ? 'selected' : '' }}>Success</option>
                        </select>
                    </div>

                    <div class="col-span-6">
                        <label for="content" class="block text-sm font-semibold text-black mb-1">Content</label>
                        <textarea id="content" name="content" rows="4" class="shadow-sm focus:ring-acetel-500 focus:border-acetel-500 block w-full sm:text-sm border-slate-300 rounded-xl px-4 py-3 transition-colors" required>{{ old('content', $announcement->content) }}</textarea>
                         @error('content') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="target_role" class="block text-sm font-semibold text-black mb-1">Target Role (Optional)</label>
                        <select id="target_role" name="target_role" class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                            <option value="">All Users</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ $announcement->target_role == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-black font-medium mt-1.5">Leave 'All Users' to broadcast to everyone.</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3"></div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="starts_at" class="block text-sm font-semibold text-black mb-1">Starts At (Optional)</label>
                        <input type="date" name="starts_at" id="starts_at" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ $announcement->starts_at ? $announcement->starts_at->format('Y-m-d') : '' }}">
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="expires_at" class="block text-sm font-semibold text-black mb-1">Expires At (Optional)</label>
                        <input type="date" name="expires_at" id="expires_at" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ $announcement->expires_at ? $announcement->expires_at->format('Y-m-d') : '' }}">
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center">
                     <div>
                         <button type="button" 
                            data-confirm="Warning: Are you sure you want to permanently delete this announcement? This action cannot be undone."
                            data-confirm-title="Delete Announcement"
                            data-confirm-type="danger"
                            data-confirm-btn="Delete Announcement"
                            onclick="document.getElementById('delete-announcement-form').submit();"
                            class="inline-flex justify-center py-2.5 px-4 border border-slate-300 shadow-sm text-sm font-bold rounded-xl text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            Delete Announcement
                        </button>
                    </div>
                    
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-acetel-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-acetel-700 focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                        Save Changes
                    </button>
                </div>
            </form>
             <form id="delete-announcement-form" action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection
