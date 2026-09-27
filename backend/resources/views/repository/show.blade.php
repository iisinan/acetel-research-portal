@extends('layouts.app')

@section('content')
<style>
    body { font-family: 'Figtree', sans-serif; }

    .show-header {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 70%, #f8fffe 100%);
        border-bottom: 1px solid rgba(22, 163, 74, 0.1);
    }

    .paper-panel {
        background: #ffffff;
        border: 1px solid rgba(22, 163, 74, 0.08);
        box-shadow: 0 40px 80px -15px rgba(22, 163, 74, 0.08), 0 20px 40px -10px rgba(0,0,0,0.04);
        border-radius: 3.5rem;
    }

    .doc-unit {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border: 1.5px solid #f1f5f9;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 2.5rem;
    }
    .doc-unit:hover {
        border-color: #bbf7d0;
        box-shadow: 0 20px 40px -12px rgba(22, 163, 74, 0.1);
        transform: translateY(-4px) scale(1.01);
    }

    .meta-badge {
        background: rgba(34, 197, 94, 0.1);
        color: #166534;
        border: 1px solid rgba(34, 197, 94, 0.1);
    }

    .btn-premium {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 20px -5px rgba(22, 163, 74, 0.3);
    }
    .btn-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(22, 163, 74, 0.4);
    }

    .abstract-text {
        color: #475569;
        line-height: 2;
        font-weight: 500;
        text-align: left;
    }

    .sidebar-panel {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        border: 1.5px solid rgba(22, 163, 74, 0.05);
        border-radius: 3rem;
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.03);
    }

    .hero-pattern {
        background-image: 
            radial-gradient(circle at 2px 2px, rgba(34, 197, 94, 0.05) 1px, transparent 0);
        background-size: 30px 30px;
    }
</style>

<div class="min-h-screen bg-white pb-40 hero-pattern relative overflow-hidden">
    <!-- Orbs -->
    <div class="floating-orb top-40 -right-40 animate-float" style="animation-duration: 12s;"></div>
    <div class="floating-orb bottom-20 -left-40 animate-float" style="animation-duration: 18s;"></div>

    <!-- Page Header -->
    <div class="show-header pt-36 pb-24 px-6 lg:px-12 relative z-10">
        <div class="max-w-7xl mx-auto">
            
            <!-- Navigation Back -->
            <div class="mb-12">
                <a href="{{ route('repository.index') }}" class="inline-flex items-center gap-3 bg-white border border-acetel-100 text-slate-500 px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:border-acetel-300 hover:text-acetel-700 transition-all shadow-sm group">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Return to Repository
                </a>
            </div>

            <!-- Metadata Row -->
            <div class="flex flex-wrap items-center gap-4 mb-10 animate-in-up">
                <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full meta-badge text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-acetel-500 animate-pulse"></span>
                    VIVA Officially Approved
                </div>
                <div class="inline-flex items-center px-5 py-2.5 rounded-full bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.25em] border border-slate-900 shadow-premium">
                    {{ $thesis->student->program->code ?? 'PROJECT' }}
                </div>
            </div>

            <!-- Main Title -->
            <h1 class="text-5xl md:text-6xl lg:text-8xl font-black text-slate-950 tracking-tighter leading-[0.95] mb-12 max-w-6xl animate-in-up">
                {{ $thesis->title }}
            </h1>

            <!-- Investigator Row -->
            <div class="flex flex-wrap items-center gap-10 pt-12 border-t border-acetel-50 animate-in-up">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-[2rem] bg-acetel-600 border border-acetel-500 flex items-center justify-center text-white font-black text-2xl shadow-premium animate-float" style="animation-duration: 8s;">
                        {{ substr($thesis->student->user->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lead Investigator</p>
                        <p class="text-xl font-black text-slate-950 leading-none tracking-tight">{{ $thesis->student->user->name }}</p>
                    </div>
                </div>

                <div class="hidden lg:block h-12 w-px bg-acetel-100"></div>

                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Academic Program</p>
                    <p class="text-xl font-black text-slate-950 leading-none tracking-tight">{{ $thesis->student->program->name }}</p>
                </div>

                <div class="ml-auto text-right hidden sm:block">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Official Archival</p>
                    <p class="text-xl font-black text-acetel-600 leading-none tracking-tight">{{ $thesis->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 lg:px-12 py-24 relative z-20">
        <div class="grid lg:grid-cols-12 gap-20">

            <!-- Primary Content -->
            <div class="lg:col-span-8 space-y-20">

                <!-- Abstract Panel -->
                <div class="paper-panel p-12 md:p-20 relative overflow-hidden animate-in-up">
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-acetel-50/50 blur-[60px] rounded-full"></div>
                    <div class="flex items-center gap-5 mb-12">
                        <div class="w-14 h-1.5 bg-acetel-600 rounded-full shadow-[0_4px_12px_rgba(22,163,74,0.3)]"></div>
                        <h2 class="text-[11px] font-black text-slate-950 uppercase tracking-[0.4em]">Institutional Abstract</h2>
                    </div>
                    <div class="abstract-text text-xl italic font-serif leading-10 text-slate-700">
                        {{ $thesis->abstract }}
                    </div>
                </div>

                <!-- Document Archives -->
                <div class="space-y-10 animate-in-up">
                    <div class="flex items-center gap-5">
                        <div class="w-1.5 h-8 bg-blue-600 rounded-full shadow-premium"></div>
                        <h2 class="text-[11px] font-black text-slate-950 uppercase tracking-[0.3em]">Verified Digital Artifacts</h2>
                    </div>

                    <div class="grid gap-6">
                        @foreach($thesis->milestones->where('status', 'approved')->sortByDesc('template.order') as $milestone)
                            @foreach($milestone->submissions as $submission)
                                <div class="doc-unit p-8 flex flex-col sm:flex-row items-center justify-between gap-10 group shadow-sm">
                                    <div class="flex items-center gap-8 min-w-0">
                                        <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all duration-500 shadow-sm shrink-0">
                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xl font-black text-slate-950 mb-1 leading-none tracking-tight">{{ $milestone->template->name }}</p>
                                            @if($submission->description)
                                                <p class="text-sm font-semibold text-slate-500 mb-2 truncate" title="{{ $submission->description }}">{{ $submission->description }}</p>
                                            @endif
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-[9px] font-black uppercase tracking-widest rounded-lg border border-blue-100">REV v{{ $submission->version }}</span>
                                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">&bull; Secure SSL Signature Verified</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 w-full sm:w-auto shrink-0 relative z-20">
                                        <button type="button"
                                            @click.prevent="$dispatch('open-document-preview', {
                                                url: '{{ route('repository.submissions.view', $submission) }}',
                                                title: '{{ addslashes($submission->file_meta['original_name'] ?? 'Version ' . $submission->version) }}',
                                                type: '{{ str_contains($submission->file_meta['mime_type'] ?? '', 'image/') ? 'image' : (($submission->file_meta['mime_type'] ?? '') === 'application/pdf' ? 'pdf' : 'other') }}'
                                            })"
                                            class="flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-5 rounded-3xl bg-white text-slate-900 font-black text-[10px] uppercase tracking-widest hover:bg-slate-950 hover:text-white border border-slate-100 shadow-sm transition-all"
                                            title="View Analysis">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Preview
                                        </button>
                                        <a href="{{ route('repository.submissions.view', $submission) }}" download
                                           class="flex-1 sm:flex-none btn-premium flex items-center justify-center gap-3 px-8 py-5 rounded-3xl text-[10px] font-black uppercase tracking-widest transition-all"
                                           title="Secure Download">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Export
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-4 space-y-12 shrink-0">

                <!-- Library Copy Access (Primary CTA) -->
                @if($thesis->library_copy)
                <div class="sidebar-panel p-10 bg-slate-950 text-white relative overflow-hidden animate-in-up">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-acetel-600/20 blur-[50px] rounded-full"></div>
                    <div class="w-16 h-16 bg-acetel-600 rounded-2xl flex items-center justify-center text-white mb-10 shadow-premium">
                        <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-4 tracking-tighter leading-none">Official Library Copy</h3>
                    <p class="text-sm text-acetel-400 font-medium leading-relaxed mb-10">
                        This is the final institutionally-cleared version of this research, approved for public archival and reference.
                    </p>
                    <a href="{{ route('repository.submissions.view', $thesis->library_copy) }}" target="_blank" class="flex items-center justify-center gap-4 w-full py-5 bg-white text-slate-900 rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.2em] hover:bg-acetel-500 hover:text-white transition-all shadow-premium">
                        Download Manuscript (PDF)
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>
                @endif

                <!-- Institutional Archival Status -->
                <div class="sidebar-panel p-10 bg-acetel-50/50 border-acetel-100 relative overflow-hidden animate-in-up" style="animation-delay: 0.1s;">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-acetel-600/10 blur-[50px] rounded-full"></div>
                    <div class="w-16 h-16 bg-white border border-acetel-100 rounded-2xl flex items-center justify-center text-acetel-600 mb-10 shadow-sm">
                        <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-950 mb-4 tracking-tighter leading-none">Archival Clearance</h3>
                    <p class="text-sm text-slate-600 font-medium leading-relaxed">
                        Institutional Milestone 13 (Library Archival) has been successfully verified for this project. This record is stored in the eternal ACETEL research ledger.
                    </p>
                </div>

                <!-- Strategic Committee Panel -->
                <div class="sidebar-panel p-10 animate-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-2 h-7 bg-acetel-600 rounded-full shadow-[0_0_10px_rgba(245,158,11,0.3)]"></div>
                        <h3 class="text-[10px] font-black text-slate-950 uppercase tracking-[0.3em]">Institutional Panel</h3>
                    </div>

                    <div class="space-y-5">
                        @foreach($thesis->assignments as $assignment)
                        <div class="flex items-center gap-5 p-5 bg-white rounded-3xl border border-slate-50 hover:bg-white hover:shadow-premium transition-all duration-500 group">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-acetel-600 font-black text-base shadow-sm group-hover:bg-acetel-600 group-hover:text-white transition-all duration-500">
                                {{ substr($assignment->supervisor->user->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-950 truncate mb-1 leading-none tracking-tight">{{ $assignment->supervisor->user->name }}</p>
                                <p class="text-[9px] font-black text-acetel-600 uppercase tracking-widest">{{ $assignment->role }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- External Archive Link -->
                <div class="flex justify-center pt-10 animate-in-up" style="animation-delay: 0.4s;">
                    <a href="{{ route('repository.index') }}" class="flex items-center gap-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] hover:text-acetel-600 transition-all border-b-2 border-transparent hover:border-acetel-200 py-1">
                        &larr; Return to Institutional Repository
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
