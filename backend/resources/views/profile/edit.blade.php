@extends(auth()->user()->hasRole('Admin') ? 'layouts.admin' : (auth()->user()->hasRole('Program Coordinator') ? 'layouts.coordinator' : 'layouts.dashboard'))

@section('header')
    Profile Settings
@endsection

@section('content')
<div class="space-y-8 animate-in-up">
    <!-- Profile & Security Header -->
    <div class="relative overflow-hidden rounded-[2.5rem] p-6 md:p-10 bg-grad-premium border border-white/20 shadow-premium group">
        <div class="absolute top-0 right-0 -mt-24 -mr-24 w-80 h-80 bg-white/10 blur-[100px] rounded-full group-hover:bg-white/15 transition-all duration-1000"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div>
                <h2 class="text-3xl font-black text-white tracking-tighter mb-2 leading-tight">Profile Management</h2>
                <p class="text-black/80 font-medium text-sm max-w-lg">Manage your account profile and password settings.</p>
            </div>
            <div class="flex gap-4">
                <div class="px-6 py-4 glass text-center border-white/10 min-w-[140px]">
                    <p class="text-[10px] font-black text-black uppercase tracking-widest mb-1">User Role</p>
                    <p class="text-2xl font-black text-white leading-tight">{{ Auth::user()->getRoleNames()->first() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Profile Details Form -->
        <x-card title="Profile Details" class="border-l-4 !border-l-acetel-500">
            <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div>
                    <label for="name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Display Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                    @error('name')
                        <p class="mt-2 text-[10px] font-black text-rose-500 uppercase tracking-widest">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                    @error('email')
                        <p class="mt-2 text-[10px] font-black text-rose-500 uppercase tracking-widest">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full md:w-auto px-8 py-4 bg-acetel-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-acetel-700 transition-all shadow-lg hover:shadow-acetel-500/20 active:scale-95">
                        Update Profile
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Cryptographic Access (Password) Form -->
        @if(!auth()->user()->must_change_password)
        <x-card title="Update Password" class="border-l-4 !border-l-acetel-900">
            <form method="post" action="{{ route('profile.password.update') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div>
                    <label for="current_password" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                    @error('current_password')
                        <p class="mt-2 text-[10px] font-black text-rose-500 uppercase tracking-widest">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">New Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                    @error('password')
                        <p class="mt-2 text-[10px] font-black text-rose-500 uppercase tracking-widest">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full md:w-auto px-8 py-4 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-acetel-600 transition-all shadow-lg hover:shadow-acetel-500/20 active:scale-95">
                        Change Password
                    </button>
                    <p class="mt-4 text-[9px] text-slate-400 font-medium italic">Changing your password will require all other active sessions to log in again.</p>
                </div>
            </form>
        </x-card>
        @endif
    </div>
</div>
@endsection
