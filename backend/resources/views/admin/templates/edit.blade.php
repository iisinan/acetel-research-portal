@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-black sm:text-3xl sm:truncate">Edit Template: {{ $template->title }}</h2>
            <p class="mt-2 text-sm text-black">Update document details or upload a new file version.</p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('admin.templates.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-xl shadow-sm text-sm font-bold text-black hover:bg-slate-50 focus:outline-none transition-colors">
                <svg class="w-4 h-4 mr-2 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Directory
            </a>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-slate-200 overflow-hidden rounded-2xl">
        <div class="px-4 py-5 sm:p-8">
            <form action="{{ route('admin.templates.update', $template) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                        <label for="title" class="block text-sm font-semibold text-black mb-1">Template Title</label>
                        <input type="text" name="title" id="title" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ old('title', $template->title) }}" required>
                        @error('title') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                         <label for="version" class="block text-sm font-semibold text-black mb-1">Version</label>
                        <input type="text" name="version" id="version" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ old('version', $template->version) }}" required>
                        @error('version') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="type" class="block text-sm font-semibold text-black mb-1">Document Type</label>
                        <select id="type" name="type" class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                            <option value="proposal" {{ $template->type == 'proposal' ? 'selected' : '' }}>Proposal</option>
                            <option value="thesis" {{ $template->type == 'thesis' ? 'selected' : '' }}>Thesis</option>
                             <option value="form" {{ $template->type == 'form' ? 'selected' : '' }}>Administrative Form</option>
                            <option value="guideline" {{ $template->type == 'guideline' ? 'selected' : '' }}>Guideline</option>
                            <option value="other" {{ $template->type == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                     <div class="col-span-6 sm:col-span-3">
                         <div class="flex items-start mt-8">
                            <div class="flex items-center h-5">
                                <input id="is_active" name="is_active" type="checkbox" class="focus:ring-acetel-500 h-4 w-4 text-acetel-600 border-slate-300 rounded" {{ $template->is_active ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_active" class="font-bold text-black">Active Status</label>
                                <p class="text-black font-medium mt-0.5">Toggle template availability for download.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-6">
                        <label for="file" class="block text-sm font-semibold text-black mb-2">Replace File (Optional)</label>
                         <div class="mt-1 flex justify-center px-6 pt-10 pb-10 border-2 border-slate-300 border-dashed rounded-xl hover:bg-slate-50 transition-colors group">
                            <div class="space-y-2 text-center">
                                <svg class="mx-auto h-12 w-12 text-black group-hover:text-acetel-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex justify-center items-center text-sm text-black">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-bold text-acetel-600 hover:text-acetel-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-acetel-500 px-1">
                                        <span>Select a new file</span>
                                        <input id="file" name="file" type="file" accept=".pdf" class="sr-only">
                                    </label>
                                    <span class="pl-1">or drag and drop here</span>
                                </div>
                                <p class="text-xs text-black font-medium uppercase tracking-tighter">PDF, DOCX, PPTX, XLS, Images up to 10MB.</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1 italic">Leave blank to keep current file ({{ basename($template->file_path) }})</p>
                            </div>
                        </div>
                         @error('file') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center">
                     <div>
                         <button type="button" 
                            onclick="if(confirm('CRITICAL ACTION: Are you sure you want to permanently delete this template? Students will no longer be able to download it.')) { document.getElementById('delete-template-form').submit(); }"
                            class="inline-flex justify-center py-2.5 px-4 border border-slate-300 shadow-sm text-sm font-bold rounded-xl text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            Delete Template
                        </button>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-acetel-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-acetel-700 focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                        Save Changes
                    </button>
                </div>
            </form>
            <form id="delete-template-form" action="{{ route('admin.templates.destroy', $template) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection
