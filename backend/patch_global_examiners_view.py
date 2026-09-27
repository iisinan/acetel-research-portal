import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

old_select_form = r"""                                    <form action="{{ route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-4">
                                        @csrf
                                        <select name="supervisor_profile_id" required class="flex-1 w-full rounded-xl border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500 shadow-sm py-2 px-3">
                                            <option value="">Select an Examiner...</option>
                                            @foreach($supervisors as $sup)
                                                <option value="{{ $sup->id }}">{{ $sup->user->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                            Assign to All
                                        </button>
                                    </form>"""

new_select_form = r"""                                    <form action="{{ route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex flex-col sm:flex-row items-start gap-4">
                                        @csrf
                                        <div class="flex-1 w-full">
                                            <select name="supervisor_profile_ids[]" multiple required class="w-full rounded-xl border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500 shadow-sm py-2 px-3 min-h-[100px]">
                                                @foreach($supervisors as $sup)
                                                    <option value="{{ $sup->id }}">{{ $sup->user->name }}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-[10px] text-slate-400 font-medium mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple examiners.</p>
                                        </div>
                                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                            Assign to All
                                        </button>
                                    </form>"""

content = content.replace(old_select_form, new_select_form)

with open(path, 'w') as f:
    f.write(content)
