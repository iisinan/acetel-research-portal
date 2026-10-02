<?php
$content = file_get_contents('resources/views/admin/users/index.blade.php');

$old = <<<HTML
                                        @if(\$role->name === 'Supervisor' && \$user->supervisorProfile)
                                            <div class="relative" x-data="{ open: false }" @click.away="open = false" @mouseleave="open = false">
                                                <button type="button" @mouseenter="open = true" @click="open = !open" class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-md bg-green-200 text-green-800 hover:bg-green-300 text-[10px] transition-colors focus:outline-none shadow-sm cursor-pointer" title="View Students">
                                                    {{ \$user->supervisorProfile->assignments->count() }}
                                                    <svg class="w-2.5 h-2.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                </button>
                                                
                                                <div x-show="open" 
                                                     x-transition:enter="transition ease-out duration-200"
                                                     x-transition:enter-start="opacity-0 translate-y-1"
                                                     x-transition:enter-end="opacity-100 translate-y-0"
                                                     x-transition:leave="transition ease-in duration-150"
                                                     x-transition:leave-start="opacity-100 translate-y-0"
                                                     x-transition:leave-end="opacity-0 translate-y-1"
                                                     class="absolute z-[60] left-1/2 -translate-x-1/2 top-full mt-2 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden" style="display: none;">
                                                    <div class="px-4 py-2 bg-slate-900 text-white flex items-center justify-between">
                                                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">Supervising</span>
                                                        <span class="text-[10px] font-black text-green-400">{{ \$user->supervisorProfile->assignments->count() }}</span>
                                                    </div>
                                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                        @forelse(\$user->supervisorProfile->assignments as \$assignment)
                                                            <div class="px-4 py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
                                                                <p class="text-xs font-bold text-slate-700 truncate capitalize">{{ strtolower(\$assignment->thesis?->student?->user?->name ?? 'Unknown Student') }}</p>
                                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ \$assignment->thesis?->student?->student_id_number ?? 'No Matric No' }}</p>
                                                            </div>
                                                        @empty
                                                            <div class="px-4 py-5 text-center">
                                                                <p class="text-[10px] font-bold text-slate-400 italic">No active students.</p>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
HTML;

$new = <<<HTML
                                        @if(\$role->name === 'Supervisor' && \$user->supervisorProfile)
                                            <div x-data="{ open: false }" class="inline-block ml-1">
                                                <button type="button" @click="open = true" class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-md bg-green-200 text-green-800 hover:bg-green-300 text-[10px] transition-colors focus:outline-none shadow-sm cursor-pointer" title="View Students">
                                                    {{ \$user->supervisorProfile->assignments->count() }}
                                                    <svg class="w-2.5 h-2.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                </button>
                                                
                                                <template x-teleport="body">
                                                    <div x-show="open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center">
                                                        <!-- Backdrop -->
                                                        <div x-show="open" 
                                                             x-transition:enter="ease-out duration-300"
                                                             x-transition:enter-start="opacity-0"
                                                             x-transition:enter-end="opacity-100"
                                                             x-transition:leave="ease-in duration-200"
                                                             x-transition:leave-start="opacity-100"
                                                             x-transition:leave-end="opacity-0"
                                                             @click="open = false" 
                                                             class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
                                                        
                                                        <!-- Modal Panel -->
                                                        <div x-show="open" 
                                                             x-transition:enter="ease-out duration-300"
                                                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                             x-transition:leave="ease-in duration-200"
                                                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                             class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden mx-4">
                                                            
                                                            <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between">
                                                                <div class="flex flex-col">
                                                                    <span class="text-xs font-black uppercase tracking-widest text-slate-300">Supervising</span>
                                                                    <span class="text-sm font-black">{{ \$user->name }}</span>
                                                                </div>
                                                                <button @click="open = false" class="p-2 -mr-2 text-slate-400 hover:text-white transition-colors rounded-full hover:bg-slate-800 focus:outline-none">
                                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                            </div>
                                                            <div class="bg-green-50 px-5 py-2 border-b border-green-100 flex justify-between items-center">
                                                                <span class="text-[10px] font-black uppercase tracking-widest text-green-700">Active Load</span>
                                                                <span class="px-2 py-0.5 rounded-md bg-green-200 text-green-800 text-[10px] font-black">{{ \$user->supervisorProfile->assignments->count() }} Students</span>
                                                            </div>
                                                            
                                                            <div class="max-h-72 overflow-y-auto custom-scrollbar p-2">
                                                                @forelse(\$user->supervisorProfile->assignments as \$assignment)
                                                                    <div class="p-3 mb-2 last:mb-0 rounded-xl border border-slate-100 hover:border-green-200 hover:bg-green-50/50 transition-all flex items-center gap-3">
                                                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                                        </div>
                                                                        <div class="min-w-0">
                                                                            <p class="text-sm font-bold text-slate-800 truncate capitalize">{{ strtolower(\$assignment->thesis?->student?->user?->name ?? 'Unknown Student') }}</p>
                                                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ \$assignment->thesis?->student?->student_id_number ?? 'No Matric No' }}</p>
                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                    <div class="px-4 py-8 text-center flex flex-col items-center">
                                                                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                                                            <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                                                        </div>
                                                                        <p class="text-xs font-bold text-slate-500">No Active Students</p>
                                                                        <p class="text-[10px] font-medium text-slate-400 mt-1">This supervisor currently has zero assignments.</p>
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        @endif
HTML;

$content = str_replace($old, $new, $content);
file_put_contents('resources/views/admin/users/index.blade.php', $content);
