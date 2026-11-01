@extends('layouts.app')

@section('content')
<style>
    /* Google Scholar Inspired Typography and Resets */
    body { font-family: Arial, sans-serif; background-color: #ffffff; }

    /* Top Search Header */
    .scholar-header {
        border-bottom: 1px solid #ebebeb;
        background: #f8f9fa;
        padding: 15px 24px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .scholar-logo-text {
        font-family: 'Figtree', sans-serif;
        font-weight: 900;
        font-size: 24px;
        color: #5f6368;
        letter-spacing: -1px;
    }
    .scholar-logo-text span {
        color: #16a34a; /* ACETEL Green integration */
    }

    .scholar-search-wrapper {
        display: flex;
        align-items: center;
        border: 1px solid #dfe1e5;
        border-radius: 4px;
        background: white;
        width: 100%;
        max-width: 600px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    
    .scholar-search-wrapper input {
        flex: 1;
        border: none;
        padding: 10px 14px;
        font-size: 16px;
        outline: none;
        color: #222;
    }

    .scholar-search-btn {
        background: #4285f4;
        border: none;
        padding: 0 18px;
        height: 100%;
        color: white;
        cursor: pointer;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .scholar-search-btn svg { width: 18px; height: 18px; }

    /* Stats Strip */
    .scholar-stats-strip {
        border-bottom: 1px solid #ebebeb;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .stats-icon {
        color: #4285f4;
        margin-right: -10px;
        padding-left: 5px;
    }

    .stats-text {
        font-size: 13px;
        color: #777;
    }

    /* Main Layout */
    .scholar-main-container {
        display: flex;
        padding: 20px 0;
        max-width: 1200px;
    }

    /* Sidebar Filters */
    .scholar-sidebar {
        width: 170px;
        flex-shrink: 0;
        padding: 0 20px 0 24px;
    }
    
    .sidebar-section {
        margin-bottom: 25px;
    }

    .sidebar-section h4 {
        font-size: 13px;
        font-weight: normal;
        color: #dd4b39; /* The faint red header color */
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .sidebar-link {
        display: block;
        font-size: 13px;
        color: #1a0dab;
        text-decoration: none;
        margin-bottom: 5px;
        line-height: 1.4;
    }
    .sidebar-link:hover { text-decoration: underline; }
    
    .sidebar-link.active {
        color: #222;
        font-weight: bold;
        text-decoration: none;
        cursor: default;
    }

    .sidebar-custom-range {
        font-size: 12px;
        color: #1a0dab;
        cursor: pointer;
        margin-top: 5px;
    }
    
    .range-inputs {
        display: none;
        margin-top: 8px;
    }
    .range-inputs.open { display: block; }
    
    .range-inputs input {
        width: 50px;
        padding: 3px 5px;
        border: 1px solid #d9d9d9;
        font-size: 12px;
        margin-bottom: 5px;
    }
    .range-inputs button {
        background: #f8f8f8;
        border: 1px solid #d9d9d9;
        padding: 3px 10px;
        font-size: 12px;
        cursor: pointer;
        color: #333;
    }

    hr.sidebar-divider {
        border: 0;
        border-top: 1px solid #ebebeb;
        margin: 15px 0;
    }

    /* Results Area */
    .scholar-results {
        flex: 1;
        padding-right: 24px;
        max-width: 1000px;
    }

    .scholar-item {
        margin-bottom: 30px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }

    .item-content {
        flex: 1;
    }

    .item-title {
        font-size: 18px;
        line-height: 1.25;
        margin-bottom: 3px;
    }
    .item-title a {
        color: #1a0dab;
        text-decoration: none;
    }
    .item-title a:hover { text-decoration: underline; }

    .item-meta {
        font-size: 13px;
        color: #006621;
        line-height: 1.4;
        margin-bottom: 4px;
    }

    .item-snippet {
        font-size: 13px;
        color: #545454;
        line-height: 1.6;
        margin-bottom: 5px;
    }
    .item-snippet.clamped {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .item-keywords {
        margin-top: 8px;
        font-size: 12px;
        font-style: italic;
        color: #006621;
    }

    .item-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: #1a0dab;
    }
    .item-actions a, .item-actions span {
        color: #1a0dab;
        text-decoration: none;
        cursor: pointer;
    }
    .item-actions a:hover { text-decoration: underline; }
    
    .action-icon {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #1a0dab;
    }

    .pdf-indicator {
        width: 180px;
        flex-shrink: 0;
        text-align: right;
    }
    
    .pdf-link-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-end;
    }

    .pdf-badge {
        font-size: 11px;
        font-weight: bold;
        color: #777;
    }
    .pdf-domain {
        color: #1a0dab;
        font-size: 13px;
        text-decoration: none;
    }
    .pdf-domain:hover { text-decoration: underline; }

    /* Custom Alpine Modal overrides */
    [x-cloak] { display: none !important; }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .scholar-header {
            flex-wrap: wrap;
            padding: 12px 16px;
            gap: 12px;
        }
        .scholar-header form {
            order: 3;
            width: 100% !important;
        }
        .scholar-search-wrapper {
            max-width: 100%;
        }
        .scholar-main-container {
            flex-direction: column;
            padding: 12px 16px;
        }
        .scholar-results {
            padding-right: 0;
            max-width: 100%;
        }
        .scholar-item {
            flex-direction: column;
            gap: 10px;
        }
        .pdf-indicator {
            width: 100%;
            text-align: left;
        }
        .pdf-link-wrapper {
            justify-content: flex-start;
        }
        .scholar-stats-strip {
            padding: 10px 16px;
            flex-wrap: wrap;
            gap: 10px;
        }
    }
</style>

<!-- Top Search Header -->
<div class="scholar-header">
    <div class="scholar-logo-text w-12 h-12 rounded-lg bg-acetel-50 overflow-hidden flex items-center justify-center border border-acetel-100/50 shadow-sm shrink-0">
        <a href="/">
            <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="ACETEL" class="w-full h-full object-cover">
        </a>
    </div>
    <div class="scholar-logo-text hidden md:block uppercase tracking-tighter">
        <a href="{{ route('repository.index') }}" style="color: inherit; text-decoration: none;">ACETEL <span>Scholar</span></a>
    </div>
    
    <form action="{{ route('repository.index') }}" method="GET" class="w-full sm:w-auto" style="flex-grow: 1;">
        <!-- Preserve existing filters -->
        @if(request('year')) <input type="hidden" name="year" value="{{ request('year') }}"> @endif
        @if(request('year_from')) <input type="hidden" name="year_from" value="{{ request('year_from') }}"> @endif
        @if(request('year_to')) <input type="hidden" name="year_to" value="{{ request('year_to') }}"> @endif
        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

        <div class="scholar-search-wrapper">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search across institutional thesis archives" autocomplete="off">
            <button type="submit" class="scholar-search-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </div>
    </form>
    
    <div class="ml-auto flex items-center gap-4">
        @auth
            <a href="{{ url('/dashboard') }}" class="text-[11px] font-bold text-slate-500 uppercase hover:underline">Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="text-[11px] font-bold text-slate-500 uppercase hover:underline">Sign In</a>
        @endauth
        <div class="w-8 h-8 bg-blue-600 rounded-full text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
        </div>
    </div>
</div>

<!-- Stats Strip -->
<div class="scholar-stats-strip">
    <div class="stats-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z" />
        </svg>
    </div>
    <span class="font-bold text-[#555] text-sm">Articles</span>
    
    <div class="stats-text ml-4">
        @if($theses->total() > 0)
            About {{ number_format($theses->total()) }} results (0.0{{ rand(1, 9) }} sec)
        @else
            No results found.
        @endif
    </div>
</div>

<div class="scholar-main-container mx-auto" x-data="{ showPdfModal: false, pdfUrl: '', pdfTitle: '' }">
    
    <!-- Sidebar -->
    <div class="scholar-sidebar hidden md:block">
        
        <!-- Year Filters -->
        <div class="sidebar-section">
            <h4>Any time</h4>
            @php
                $currentYear = date('Y');
                
                // Helper to generate filter links keeping search/sort but changing year params
                function buildFilterUrl($yearParam, $yearValue) {
                    $params = request()->except(['page', 'year', 'year_from', 'year_to']);
                    if ($yearParam) {
                        $params[$yearParam] = $yearValue;
                    }
                    return route('repository.index', $params);
                }
            @endphp
            
            <a href="{{ buildFilterUrl(null, null) }}" class="sidebar-link {{ !request('year') && !request('year_from') ? 'active' : '' }}">Any time</a>
            
            <a href="{{ buildFilterUrl('year_from', $currentYear) }}" class="sidebar-link {{ request('year_from') == $currentYear ? 'active' : '' }}">Since {{ $currentYear }}</a>
            
            <a href="{{ buildFilterUrl('year_from', $currentYear - 1) }}" class="sidebar-link {{ request('year_from') == ($currentYear - 1) ? 'active' : '' }}">Since {{ $currentYear - 1 }}</a>
            
            <a href="{{ buildFilterUrl('year_from', $currentYear - 3) }}" class="sidebar-link {{ request('year_from') == ($currentYear - 3) ? 'active' : '' }}">Since {{ $currentYear - 3 }}</a>
            
            <div x-data="{ open: {{ request('year_from') || request('year_to') && !request('year') && request('year_from') != $currentYear && request('year_from') != $currentYear-1 && request('year_from') != $currentYear-3 ? 'true' : 'false' }} }">
                <div @click="open = !open" class="sidebar-custom-range">Custom range...</div>
                
                <form action="{{ route('repository.index') }}" method="GET" class="range-inputs" :class="open ? 'open' : ''">
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                    
                    <input type="text" name="year_from" placeholder="From" value="{{ request('year_from') }}" autocomplete="off">
                    <input type="text" name="year_to" placeholder="To" value="{{ request('year_to') }}" autocomplete="off">
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>
        

        
        <!-- Labels -->
        <div class="sidebar-section text-[13px] text-[#555] space-y-2">
            <label class="flex items-center gap-2">
                <input type="checkbox" disabled> include patents
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" checked disabled> include citations
            </label>
        </div>

    </div>

    <!-- Results Area -->
    <div class="scholar-results">
        
        <!-- Mobile Filter Chips -->
        <div class="md:hidden flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-4 -mx-1 px-1">
            <a href="{{ buildFilterUrl(null, null) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap {{ !request('year') && !request('year_from') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Any time</a>
            <a href="{{ buildFilterUrl('year_from', $currentYear) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap {{ request('year_from') == $currentYear ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Since {{ $currentYear }}</a>
            <a href="{{ buildFilterUrl('year_from', $currentYear - 1) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap {{ request('year_from') == ($currentYear - 1) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Since {{ $currentYear - 1 }}</a>
            <a href="{{ buildFilterUrl('year_from', $currentYear - 3) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap {{ request('year_from') == ($currentYear - 3) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Since {{ $currentYear - 3 }}</a>
        </div>

        @forelse($theses as $thesis)
            <div class="scholar-item" x-data="{ expanded: false }">
                <div class="item-content">
                    <h3 class="item-title">
                        <a href="{{ route('repository.show', $thesis) }}">
                            {{ $thesis->title }}
                        </a>
                    </h3>
                    
                    <div class="item-meta">
                        <!-- Format: Authors - Journal/Program, Year - Publisher/Domain -->
                        @php
                            $authorInitial = strtoupper(substr($thesis->student->user->name, 0, 1));
                            $authorLastName = explode(' ', $thesis->student->user->name);
                            $authorLastName = end($authorLastName);
                            $authorStr = "{$authorInitial} {$authorLastName}";
                            
                            $year = $thesis->created_at->format('Y');
                            $program = $thesis->student->program->name;
                        @endphp
                        {{ $authorStr }} - {{ $program }}, {{ $year }} - repository.acetel.edu.ng
                    </div>
                    
                    <div class="item-snippet transition-all duration-300" :class="expanded ? '' : 'clamped'">
                        {{ $thesis->abstract }}
                    </div>
                    
                    <div x-show="expanded" class="item-keywords" x-transition.opacity>
                        @if($thesis->keywords)
                            <span class="font-bold">Keywords:</span> {{ $thesis->keywords }}
                        @else
                            <span class="font-bold">Keywords:</span> None available
                        @endif
                    </div>
                    
                    <div class="item-actions mt-3" x-show="expanded" x-transition>
                        <a href="{{ route('repository.index', ['search' => $thesis->keywords ?: $thesis->title]) }}" class="hover:underline">Related articles</a>
                        
                        @if($thesis->library_copy)
                            <a href="#" @click.prevent="pdfUrl = '{{ route('repository.submissions.view', $thesis->library_copy) }}'; pdfTitle = '{{ addslashes($thesis->title) }}'; showPdfModal = true" class="ml-4 flex items-center gap-1 text-[#dd4b39] font-bold">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                Read Document [PDF]
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="pdf-indicator hidden sm:block">
                    @if($thesis->library_copy)
                        <div class="pdf-link-wrapper">
                            <span class="pdf-badge">[PDF]</span>
                            <a href="#" @click.prevent="pdfUrl = '{{ route('repository.submissions.view', $thesis->library_copy) }}'; pdfTitle = '{{ addslashes($thesis->title) }}'; showPdfModal = true" class="pdf-domain">acetel.edu.ng</a>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center" style="max-width: 600px; margin: 0 auto;">
                <p style="font-size: 16px; margin-bottom: 20px;">Your search - <b>{{ request('search') }}</b> - did not match any documents.</p>
                <p style="text-align: left; font-size: 16px;">Suggestions:</p>
                <ul style="text-align: left; margin-left: 30px; margin-top: 10px; line-height: 1.6;">
                    <li>Make sure that all words are spelled correctly.</li>
                    <li>Try different keywords.</li>
                    <li>Try more general keywords.</li>
                    <li>Try lowering the temporal filter constraints.</li>
                </ul>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($theses->hasPages())
            <div class="mt-8 pb-10 flex items-center justify-center gap-1">
                {{-- Simplified academic pagination styling --}}
                <div class="flex items-center" style="font-size: 24px; color: #4285f4; font-weight: bold; letter-spacing: 2px;">
                    <span style="color: #ea4335">G</span>
                    <span style="color: #fbbc05">o</span>
                    @for ($i = 1; $i <= min($theses->lastPage(), 10); $i++)
                        <a href="{{ $theses->url($i) }}" style="text-decoration: none; {{ $i == $theses->currentPage() ? 'color: #ea4335;' : 'color: #fbbc05; cursor: pointer; text-decoration: underline;' }}">o</a>
                    @endfor
                    <span style="color: #4285f4">g</span>
                    <span style="color: #34a853">l</span>
                    <span style="color: #ea4335">e</span>
                </div>
            </div>
            
            <div class="flex items-center justify-center gap-4 text-[13px] text-[#1a0dab] mb-20 font-bold">
                @if(!$theses->onFirstPage())
                    <a href="{{ $theses->previousPageUrl() }}" class="hover:underline">&lsaquo; Previous</a>
                @endif
                @if($theses->hasMorePages())
                    <a href="{{ $theses->nextPageUrl() }}" class="hover:underline">Next &rsaquo;</a>
                @endif
            </div>
        @endif
        
    </div>

    <!-- PDF Preview Modal -->
    <div x-show="showPdfModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition.opacity>
        <div @click.away="showPdfModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[90vh] flex flex-col overflow-hidden" x-show="showPdfModal" x-transition.scale.origin.bottom>
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80 backdrop-blur-md">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg truncate tracking-tight" x-text="pdfTitle"></h3>
                </div>
                <button @click="showPdfModal = false" class="w-10 h-10 bg-white border border-gray-200 text-gray-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 rounded-xl flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <!-- Modal Body (iframe) -->
            <div class="flex-1 bg-gray-100 relative">
                <iframe :src="pdfUrl" class="absolute inset-0 w-full h-full border-0"></iframe>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-100 bg-white flex justify-end gap-3 shrink-0">
                <a :href="pdfUrl" download class="flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-[11px] font-black uppercase tracking-[0.15em] rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Direct PDF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
