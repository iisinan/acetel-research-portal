@if(session()->has('impersonate_admin_id') && auth()->check())
    @php
        $impersonatedUser = auth()->user();
        $impersonatedRoles = $impersonatedUser->getRoleNames();
    @endphp
    <div class="bg-slate-900 text-white px-4 sm:px-6 py-2.5 border-b border-amber-400/50 shadow-md z-40 shrink-0">
        <div class="max-w-[1400px] mx-auto flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5 min-w-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-widest shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-950 animate-pulse"></span>
                    Admin View Mode
                </span>
                <p class="text-xs sm:text-sm font-bold text-slate-100 truncate">
                    Viewing as <span class="text-amber-300 font-black">{{ $impersonatedUser->name }}</span>
                    <span class="text-slate-400 font-medium text-xs hidden sm:inline">({{ $impersonatedUser->email }})</span>
                </p>
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach($impersonatedRoles as $impRole)
                        @if(in_array($impRole, ['Student', 'Supervisor', 'Internal Examiner', 'External Examiner', 'Program Coordinator', 'Director', 'Admin']))
                            <a href="{{ route('dashboard', ['role' => $impRole]) }}"
                               class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border transition-colors {{ request()->query('role') === $impRole ? 'bg-amber-400 text-slate-950 border-amber-300' : 'bg-slate-800 text-slate-200 border-slate-700 hover:bg-slate-700' }}"
                               title="Switch to {{ $impRole }} dashboard view">
                                {{ $impRole }}
                            </a>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 border border-slate-700 text-[10px] font-black uppercase tracking-wider">
                                {{ $impRole }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-[10px] font-black uppercase tracking-wider transition-colors">
                    User Dashboard
                </a>
                <form method="POST" action="{{ route('impersonation.leave') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider transition-all shadow-sm cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" /></svg>
                        <span>Return to Admin ({{ session('impersonate_admin_name', 'Admin') }})</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
