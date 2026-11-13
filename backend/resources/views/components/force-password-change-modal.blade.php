@if (auth()->check() && auth()->user()->must_change_password && !request()->is('profile*') && !session()->has('impersonate_admin_id'))
    <div x-data="{ open: true }"
         x-show="open"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true"
         style="display: none;">
        
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="fixed inset-0 transition-opacity bg-slate-900/80 backdrop-blur-sm"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 class="inline-block relative overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[2rem] shadow-premium sm:my-8 sm:align-middle sm:max-w-md w-full border border-slate-100 p-8 sm:p-10 pointer-events-auto">

                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 text-red-600 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-2 tracking-tight" id="modal-title">Security Update Required</h3>
                    <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed">
                        Welcome to the ACETEL platform. For your security, please update your password before proceeding further.
                    </p>

                    <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-6 text-left">
                        @csrf
                        @method('patch')

                        <input type="hidden" name="current_password" value="password" />

                        <div>
                            <label for="password" class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">New Password</label>
                            <input type="password" name="password" id="password" required autocomplete="new-password"
                                   class="block w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-0 focus:border-brand-500 transition-colors placeholder:text-slate-400 placeholder:font-medium">
                            @error('password')
                                <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                                   class="block w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-0 focus:border-brand-500 transition-colors placeholder:text-slate-400 placeholder:font-medium">
                            @error('password_confirmation')
                                <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center items-center gap-3 w-full py-4 bg-brand-600 text-white rounded-xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-brand-700 transition-all shadow-premium">
                                Update Security Key
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
