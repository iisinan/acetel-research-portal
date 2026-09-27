@extends(auth()->user()->hasRole('Admin') ? 'layouts.admin' : (auth()->user()->hasRole('Program Coordinator') ? 'layouts.coordinator' : 'layouts.dashboard'))

@section('header')
    Compose Message
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <!-- Back Navigation -->
    <a href="{{ route('inbox.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-xs font-black text-slate-700 uppercase tracking-widest transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
        Inbox
    </a>

    <!-- Compose Card -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/30">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary-600 text-white flex items-center justify-center shadow-lg shadow-primary-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">New Message</h2>
                    <p class="text-xs font-medium text-slate-500">Compose and send a message.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('inbox.store') }}" method="POST" enctype="multipart/form-data" class="px-10 py-8 space-y-6" 
            x-data="{ 
                showCC: false, 
                showBCC: false,
                allRecipients: @js($recipients),
                to: [],
                cc: [],
                bcc: [],
                searchTo: '',
                searchCC: '',
                searchBCC: '',
                openTo: false,
                openCC: false,
                openBCC: false,
                selectedFiles: [],

                init() {
                    const replyTo = '{{ request('reply_to') }}';
                    if (replyTo) {
                        const r = this.allRecipients.find(u => u.id == replyTo);
                        if (r) this.to.push(r);
                    }
                },

                add(user, type) {
                    if (type === 'to' && !this.to.find(u => u.id === user.id)) this.to.push(user);
                    if (type === 'cc' && !this.cc.find(u => u.id === user.id)) this.cc.push(user);
                    if (type === 'bcc' && !this.bcc.find(u => u.id === user.id)) this.bcc.push(user);
                    
                    this['search'+type.charAt(0).toUpperCase() + type.slice(1)] = '';
                    this['open'+type.charAt(0).toUpperCase() + type.slice(1)] = false;
                },

                remove(id, type) {
                    this[type] = this[type].filter(u => u.id !== id);
                },

                filtered(search, type) {
                    if (!search) return this.allRecipients.filter(u => !this[type].find(s => s.id === u.id));
                    return this.allRecipients.filter(u => 
                        !this[type].find(s => s.id === u.id) && 
                        u.name.toLowerCase().includes(search.toLowerCase())
                    );
                },

                onFileChange(e) {
                    const files = Array.from(e.target.files);
                    files.forEach(file => {
                        if (!this.selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                            this.selectedFiles.push(file);
                        }
                    });
                },

                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                },

                formatSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }">
            @csrf

            <!-- Recipients Configuration -->
            <div class="space-y-4">
                <!-- To Field -->
                <div class="relative">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">To</label>
                        <div class="flex gap-3">
                            <button type="button" @click="showCC = !showCC" class="text-[10px] font-black uppercase tracking-widest transition-colors" :class="showCC ? 'text-primary-600' : 'text-slate-400 hover:text-slate-600'">CC</button>
                            <button type="button" @click="showBCC = !showBCC" class="text-[10px] font-black uppercase tracking-widest transition-colors" :class="showBCC ? 'text-primary-600' : 'text-slate-400 hover:text-slate-600'">BCC</button>
                        </div>
                    </div>
                    
                    <div class="min-h-[56px] p-2 bg-slate-50 border border-slate-200 rounded-xl flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-primary-500 transition-all cursor-text" @click="$refs.inputTo.focus()">
                        <template x-for="user in to" :key="user.id">
                            <div class="flex items-center gap-1.5 pl-3 pr-2 py-1.5 bg-white border border-slate-200 rounded-lg shadow-sm animate-in fade-in zoom-in duration-200">
                                <span class="text-xs font-bold text-slate-700" x-text="user.name"></span>
                                <button type="button" @click.stop="remove(user.id, 'to')" class="p-0.5 hover:bg-rose-50 hover:text-rose-500 rounded-md transition-colors text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <input type="hidden" name="to[]" :value="user.id">
                            </div>
                        </template>
                        <input type="text" x-ref="inputTo" x-model="searchTo" @focus="openTo = true" @click.stop 
                            class="flex-1 min-w-[120px] bg-transparent border-none focus:ring-0 text-sm font-semibold text-slate-900 placeholder:text-slate-300"
                            placeholder="Add recipient...">
                    </div>

                    <!-- Dropdown -->
                    <div x-show="openTo && filtered(searchTo, 'to').length > 0" 
                        @click.away="openTo = false"
                        class="absolute z-[100] left-0 right-0 mt-2 max-h-60 overflow-y-auto bg-white border border-slate-100 rounded-2xl shadow-2xl shadow-slate-200/50 p-2 space-y-1 animate-in slide-in-from-top-2 duration-200" x-cloak>
                        <template x-for="user in filtered(searchTo, 'to')" :key="user.id">
                            <button type="button" @click="add(user, 'to')" class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-slate-50 text-left transition-colors group">
                                <div>
                                    <p class="text-sm font-bold text-slate-900" x-text="user.name"></p>
                                    <p class="text-[10px] font-medium text-slate-400" x-text="user.email"></p>
                                </div>
                                <svg class="w-4 h-4 text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            </button>
                        </template>
                    </div>
                    @error('to') <p class="text-rose-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                </div>

                <!-- CC Field -->
                <div x-show="showCC" x-transition x-cloak class="relative">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">CC</label>
                    <div class="min-h-[56px] p-2 bg-slate-50 border border-slate-200 rounded-xl flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-primary-500 transition-all cursor-text" @click="$refs.inputCC.focus()">
                        <template x-for="user in cc" :key="user.id">
                            <div class="flex items-center gap-1.5 pl-3 pr-2 py-1.5 bg-white border border-slate-200 rounded-lg shadow-sm animate-in fade-in zoom-in duration-200">
                                <span class="text-xs font-bold text-slate-700" x-text="user.name"></span>
                                <button type="button" @click.stop="remove(user.id, 'cc')" class="p-0.5 hover:bg-rose-50 hover:text-rose-500 rounded-md transition-colors text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <input type="hidden" name="cc[]" :value="user.id">
                            </div>
                        </template>
                        <input type="text" x-ref="inputCC" x-model="searchCC" @focus="openCC = true" @click.stop
                            class="flex-1 min-w-[120px] bg-transparent border-none focus:ring-0 text-sm font-semibold text-slate-900 placeholder:text-slate-300"
                            placeholder="Add CC recipient...">
                    </div>
                    <div x-show="openCC && filtered(searchCC, 'cc').length > 0" 
                        @click.away="openCC = false"
                        class="absolute z-[90] left-0 right-0 mt-2 max-h-60 overflow-y-auto bg-white border border-slate-100 rounded-2xl shadow-2xl shadow-slate-200/50 p-2 space-y-1 animate-in slide-in-from-top-2 duration-200" x-cloak>
                        <template x-for="user in filtered(searchCC, 'cc')" :key="user.id">
                            <button type="button" @click="add(user, 'cc')" class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-slate-50 text-left transition-colors group">
                                <div>
                                    <p class="text-sm font-bold text-slate-900" x-text="user.name"></p>
                                    <p class="text-[10px] font-medium text-slate-400" x-text="user.email"></p>
                                </div>
                                <svg class="w-4 h-4 text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- BCC Field -->
                <div x-show="showBCC" x-transition x-cloak class="relative">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">BCC</label>
                    <div class="min-h-[56px] p-2 bg-slate-50 border border-slate-200 rounded-xl flex flex-wrap gap-2 items-center focus-within:ring-2 focus-within:ring-primary-500 transition-all cursor-text" @click="$refs.inputBCC.focus()">
                        <template x-for="user in bcc" :key="user.id">
                            <div class="flex items-center gap-1.5 pl-3 pr-2 py-1.5 bg-white border border-slate-200 rounded-lg shadow-sm animate-in fade-in zoom-in duration-200">
                                <span class="text-xs font-bold text-slate-700" x-text="user.name"></span>
                                <button type="button" @click.stop="remove(user.id, 'bcc')" class="p-0.5 hover:bg-rose-50 hover:text-rose-500 rounded-md transition-colors text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <input type="hidden" name="bcc[]" :value="user.id">
                            </div>
                        </template>
                        <input type="text" x-ref="inputBCC" x-model="searchBCC" @focus="openBCC = true" @click.stop
                            class="flex-1 min-w-[120px] bg-transparent border-none focus:ring-0 text-sm font-semibold text-slate-900 placeholder:text-slate-300"
                            placeholder="Add BCC recipient...">
                    </div>
                    <div x-show="openBCC && filtered(searchBCC, 'bcc').length > 0" 
                        @click.away="openBCC = false"
                        class="absolute z-80 left-0 right-0 mt-2 max-h-60 overflow-y-auto bg-white border border-slate-100 rounded-2xl shadow-2xl shadow-slate-200/50 p-2 space-y-1 animate-in slide-in-from-top-2 duration-200" x-cloak>
                        <template x-for="user in filtered(searchBCC, 'bcc')" :key="user.id">
                            <button type="button" @click="add(user, 'bcc')" class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-slate-50 text-left transition-colors group">
                                <div>
                                    <p class="text-sm font-bold text-slate-900" x-text="user.name"></p>
                                    <p class="text-[10px] font-medium text-slate-400" x-text="user.email"></p>
                                </div>
                                <svg class="w-4 h-4 text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Subject</label>
                <input type="text" name="subject" id="subject" required maxlength="255"
                    value="{{ old('subject', request('subject')) }}"
                    placeholder="Message subject..."
                    class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all placeholder:text-slate-300">
                @error('subject') <p class="text-rose-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
            </div>

            <!-- Body -->
            <div>
                <label for="body" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Message Body</label>
                <textarea name="body" id="body" rows="6" required maxlength="5000"
                    placeholder="Write your message here..."
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all resize-none placeholder:text-slate-300 leading-relaxed">{{ old('body') }}</textarea>
                @error('body') <p class="text-rose-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
            </div>

            <!-- Attachments Panel -->
            <div x-show="selectedFiles.length > 0" x-transition class="space-y-3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Attached Documents</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="(file, index) in selectedFiles" :key="index">
                        <div class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-2xl shadow-sm group hover:border-primary-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-primary-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-900 truncate max-w-[150px]" x-text="file.name"></p>
                                    <p class="text-[10px] font-medium text-slate-400" x-text="formatSize(file.size)"></p>
                                </div>
                            </div>
                            <button type="button" @click="removeFile(index)" class="p-2 text-slate-300 hover:text-rose-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <div class="flex items-center gap-4">
                    <input type="file" multiple id="attachments" name="attachments[]" 
                        class="hidden" accept=".pdf" x-ref="attachments" @change="onFileChange($event)">
                    <button type="button" @click="$refs.attachments.click()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 hover:bg-slate-100 text-xs font-black text-slate-700 uppercase tracking-widest transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        Attach
                    </button>
                    <a href="{{ route('inbox.index') }}" class="text-xs font-black text-slate-500 uppercase tracking-widest hover:text-slate-900 transition-colors ml-2">Discard</a>
                </div>
                <button type="submit" class="inline-flex items-center gap-3 px-8 py-4 bg-primary-600 rounded-xl text-xs font-black text-white uppercase tracking-widest hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/20 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
