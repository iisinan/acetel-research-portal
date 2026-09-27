import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

old_select_form = r"""                                            <form action="{{ route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex items-center gap-2 sm:ml-auto">
                                                @csrf
                                                <select name="supervisor_profile_id" required class="block py-1.5 pl-3 pr-8 text-xs border-indigo-200 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg">
                                                    <option value="">Select Examiner</option>
                                                    @foreach($supervisors as $sup)
                                                        <option value="{{ $sup->id }}" {{ $currentExaminer && $currentExaminer->user_id == $sup->user_id ? 'selected' : '' }}>
                                                            {{ $sup->user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                    Assign to All
                                                </button>
                                            </form>"""

new_select_form = r"""                                            <form action="{{ route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex items-center gap-2 sm:ml-auto overflow-visible relative">
                                                @csrf
                                                <!-- Alpine component for multiselect -->
                                                <div x-data="{
                                                    options: [
                                                        @foreach($supervisors as $sup)
                                                            { id: '{{ $sup->id }}', name: '{{ addslashes($sup->user->name) }}' }{{ !$loop->last ? ',' : '' }}
                                                        @endforeach
                                                    ],
                                                    selected: [],
                                                    open: false,
                                                    get selectedNames() {
                                                        if (this.selected.length === 0) return 'Select Examiner(s)';
                                                        if (this.selected.length === 1) return this.options.find(o => o.id == this.selected[0])?.name || '';
                                                        return this.selected.length + ' selected';
                                                    }
                                                }" class="relative w-48 z-50">
                                                    
                                                    <div @click="open = !open" class="block w-full py-1.5 pl-3 pr-8 text-xs border border-indigo-200 bg-white rounded-lg cursor-pointer flex items-center justify-between">
                                                        <span class="truncate text-slate-700" x-text="selectedNames"></span>
                                                        <svg class="w-3 h-3 text-slate-400 absolute right-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    </div>

                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 w-64 mt-1 bg-white border border-indigo-100 rounded-lg shadow-xl max-h-60 overflow-y-auto z-50" style="display: none;">
                                                        <div class="p-2 sticky top-0 bg-slate-50 border-b border-slate-100 flex justify-between gap-2 z-10">
                                                            <button type="button" @click.stop="selected = options.map(o => o.id)" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider px-2 py-1 bg-indigo-50 hover:bg-indigo-100 rounded flex-1">Select All</button>
                                                            <button type="button" @click.stop="selected = []" class="text-[10px] font-bold text-slate-500 hover:text-slate-700 uppercase tracking-wider px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded flex-1">Clear</button>
                                                        </div>
                                                        <div class="py-1">
                                                            <template x-for="option in options" :key="option.id">
                                                                <label class="flex items-center px-3 py-2 hover:bg-indigo-50 cursor-pointer">
                                                                    <input type="checkbox" :value="option.id" x-model="selected" name="supervisor_profile_ids[]" class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 mr-2 w-3.5 h-3.5">
                                                                    <span class="text-xs text-slate-700" x-text="option.name"></span>
                                                                </label>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg text-xs font-bold transition-colors shadow-sm shrink-0">
                                                    Assign to All
                                                </button>
                                            </form>"""

content = content.replace(old_select_form, new_select_form)
with open(path, 'w') as f:
    f.write(content)
