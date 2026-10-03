@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-black sm:text-3xl sm:truncate">Upload Document Template</h2>
            <p class="mt-2 text-sm text-black">Add a new standard form, proposal guideline, or thesis template.</p>
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
            <form action="{{ route('admin.templates.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                        <label for="title" class="block text-sm font-semibold text-black mb-1">Template Title</label>
                        <input type="text" name="title" id="title" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ old('title') }}" placeholder="e.g. Masters Thesis Formatting Guide" required>
                        @error('title') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                         <label for="version" class="block text-sm font-semibold text-black mb-1">Version</label>
                        <input type="text" name="version" id="version" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ old('version', '1.0') }}" required>
                        @error('version') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="type" class="block text-sm font-semibold text-black mb-1">Document Type</label>
                        <select id="type" name="type" class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                            <option value="proposal">Proposal</option>
                            <option value="thesis">Thesis</option>
                            <option value="form">Administrative Form</option>
                            <option value="guideline">Guideline</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-span-6">
                        <label for="file" class="block text-sm font-semibold text-black mb-2">File Upload</label>
                        <div class="mt-1 flex justify-center px-6 pt-10 pb-10 border-2 border-slate-300 border-dashed rounded-xl hover:bg-slate-50 transition-colors group">
                            <div class="space-y-2 text-center">
                                <svg class="mx-auto h-12 w-12 text-black group-hover:text-acetel-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex justify-center items-center text-sm text-black">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-bold text-acetel-600 hover:text-acetel-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-acetel-500 px-1">
                                        <span>Select a file</span>
                                        <input id="file" name="file" type="file" accept=".pdf" class="sr-only" required>
                                    </label>
                                    <span class="pl-1">or drag and drop here</span>
                                </div>
                                <p class="text-xs text-black font-medium uppercase tracking-tighter">PDF, DOCX, PPTX, XLS, Images up to 10MB.</p>
                            </div>
                        </div>
                         @error('file') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-acetel-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-acetel-700 focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Upload Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
