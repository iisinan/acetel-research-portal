@extends('layouts.coordinator')

@section('header', 'Examiners Pool')

@section('content')
<div x-data="{ showInternalModal: false, showExternalModal: false }" class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 pb-6 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50 border border-acetel-100/50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Quality Assurance</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Examiners Pool</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Manage approved panel members for institutional defense events.</p>
        </div>
        <div class="flex items-center gap-3 relative z-20">
            <button @click="showInternalModal = true" class="px-5 py-3 rounded-2xl bg-white text-slate-700 hover:text-acetel-600 hover:border-acetel-200 transition-all text-[10px] font-black uppercase tracking-widest border border-slate-200 shadow-sm flex items-center gap-2 group cursor-pointer">
                <span class="w-5 h-5 rounded-md bg-slate-50 group-hover:bg-acetel-50 border border-slate-100 flex items-center justify-center transition-colors">+</span>
                Internal Member
            </button>
            <button @click="showExternalModal = true" class="px-5 py-3 rounded-2xl bg-slate-900 text-white hover:bg-acetel-600 transition-all text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-900/10 flex items-center gap-2 group border border-slate-800 hover:border-acetel-500 cursor-pointer">
                <span class="w-5 h-5 rounded-md bg-white/10 group-hover:bg-white/20 border border-white/10 flex items-center justify-center transition-colors">+</span>
                External Member
            </button>
        </div>
    </div>

    <!-- Telemetry Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Internal Examiners -->
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden flex flex-col">
            <div class="absolute top-0 right-0 w-32 h-32 bg-acetel-50 rounded-full blur-[40px] -mr-16 -mt-16 z-0"></div>
            
            <div class="px-8 py-8 border-b border-slate-50 flex justify-between items-center bg-transparent relative z-10">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Internal Faculty</h2>
                    <p class="text-[9px] font-black text-acetel-500 uppercase tracking-[0.2em] mt-1">Institutional Pool</p>
                </div>
                <div class="px-4 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-[10px] font-black text-slate-500 tracking-widest shadow-inner">
                    {{ $internalExaminers->count() }} ACTIVE
                </div>
            </div>
            
            <div class="p-8 space-y-4 flex-1 bg-slate-50/30 relative z-10">
                @forelse($internalExaminers as $e)
                    <div class="flex items-center justify-between p-5 bg-white border border-slate-100 rounded-[1.5rem] hover:border-acetel-200 hover:shadow-lg hover:shadow-acetel-500/5 transition-all group">
                        <div class="flex items-center gap-5 min-w-0">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center font-black text-sm border border-slate-100 group-hover:bg-acetel-50 group-hover:text-acetel-600 transition-colors shrink-0">
                                {{ substr($e->user->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $e->user->name }}</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate">{{ $e->user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0 pl-4 border-l border-slate-50">
                            <form action="{{ route('coordinator.examiners.toggle', ['profile' => $e->id, 'type' => 'internal']) }}" method="POST">
                                @csrf
                                <button type="submit" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-acetel-600 focus:ring-offset-2 {{ $e->active ? 'bg-acetel-500' : 'bg-slate-200' }}" role="switch" aria-checked="{{ $e->active ? 'true' : 'false' }}">
                                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $e->active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 bg-white rounded-[1.5rem] border border-dashed border-slate-200">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No internal examiners configured.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- External Examiners -->
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden flex flex-col">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-full blur-[40px] -mr-16 -mt-16 z-0"></div>
            
            <div class="px-8 py-8 border-b border-slate-50 flex justify-between items-center bg-transparent relative z-10">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">External Partners</h2>
                    <p class="text-[9px] font-black text-purple-500 uppercase tracking-[0.2em] mt-1">Cross-Institutional</p>
                </div>
                <div class="px-4 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-[10px] font-black text-slate-500 tracking-widest shadow-inner">
                    {{ $externalExaminers->count() }} ACTIVE
                </div>
            </div>
            
            <div class="p-8 space-y-4 flex-1 bg-slate-50/30 relative z-10">
                @forelse($externalExaminers as $e)
                    <div class="flex items-center justify-between p-5 bg-white border border-slate-100 rounded-[1.5rem] hover:border-purple-200 hover:shadow-lg hover:shadow-purple-500/5 transition-all group">
                        <div class="flex items-center gap-5 min-w-0">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center font-black text-sm border border-slate-100 group-hover:bg-purple-50 group-hover:text-purple-600 transition-colors shrink-0">
                                {{ substr($e->user->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $e->user->name }}</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate">{{ $e->institution }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0 pl-4 border-l border-slate-50">
                            <form action="{{ route('coordinator.examiners.toggle', ['profile' => $e->id, 'type' => 'external']) }}" method="POST">
                                @csrf
                                <button type="submit" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-600 focus:ring-offset-2 {{ $e->active ? 'bg-purple-500' : 'bg-slate-200' }}" role="switch" aria-checked="{{ $e->active ? 'true' : 'false' }}">
                                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $e->active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 bg-white rounded-[1.5rem] border border-dashed border-slate-200">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No external examiners configured.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AlpineJS Modals -->
    <!-- Internal Examiner Modal -->
        <div x-show="showInternalModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="showInternalModal = false" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl w-full max-w-lg overflow-hidden animate-in-up">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Onboard Internal</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Add existing faculty to pool</p>
                    </div>
                    <button @click="showInternalModal = false" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" /></svg>
                    </button>
                </div>
                
                <form action="{{ route('coordinator.examiners.storeInternal') }}" method="POST" class="p-10 space-y-8">
                    @csrf
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-4 pl-1">Select Supervisor</label>
                        <div class="relative">
                            <select name="supervisor_id" class="w-full bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 py-4 pl-5 pr-12 appearance-none shadow-sm" required>
                                <option value="">-- Choose verified staff --</option>
                                @foreach($potentialInternal as $p)
                                    <option value="{{ $p->id }}">{{ $p->user->name }} ({{ $p->specialization ?? 'General' }})</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                        <p class="text-[10px] font-semibold text-slate-500 leading-relaxed mt-4 px-2">Only recognized supervisors mapped to your programs are eligible for internal examination boards.</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex gap-4">
                        <button type="button" @click="showInternalModal = false" class="flex-1 py-4 bg-slate-50 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancel</button>
                        <button type="submit" class="flex-[2] py-4 bg-acetel-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-acetel-500/20 hover:bg-acetel-700 active:scale-95 transition-all">Add to Pool</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- External Examiner Modal -->
        <div x-show="showExternalModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm outline-none">
            <div @click.away="showExternalModal = false" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl w-full max-w-lg overflow-hidden animate-in-up flex flex-col md:max-h-[90vh]">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Onboard External</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Register cross-institutional partner</p>
                    </div>
                    <button @click="showExternalModal = false" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" /></svg>
                    </button>
                </div>
                
                <form action="{{ route('coordinator.examiners.storeExternal') }}" method="POST" class="flex-1 flex flex-col min-h-0">
                    @csrf
                    <div class="p-10 space-y-6 overflow-y-auto">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-3 pl-1">Full Name</label>
                            <input type="text" name="name" placeholder="Prof. Jane Doe" class="w-full bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 py-3.5 px-5 shadow-sm placeholder:text-slate-300" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-3 pl-1">Email Address</label>
                            <input type="email" name="email" placeholder="jane.doe@university.edu" class="w-full bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 py-3.5 px-5 shadow-sm placeholder:text-slate-300" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-3 pl-1">Institution</label>
                            <input type="text" name="institution" placeholder="e.g. University of Lagos" class="w-full bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 py-3.5 px-5 shadow-sm placeholder:text-slate-300" required>
                        </div>
                    </div>
                    <div class="px-10 py-8 border-t border-slate-50 flex gap-4 bg-white shrink-0">
                        <button type="button" @click="showExternalModal = false" class="flex-1 py-4 bg-slate-50 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancel</button>
                        <button type="submit" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-900/10 hover:bg-purple-600 hover:shadow-purple-500/20 active:scale-95 transition-all">Register & Add</button>
                    </div>
                </form>
            </div>
        </div>
</div>
@endsection
