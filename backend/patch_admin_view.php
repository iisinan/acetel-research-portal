<?php
$content = file_get_contents('resources/views/admin/cohorts/register_students.blade.php');

$old = <<<EOD
                        <div>
                            <label for="program_id" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Program <span class="text-rose-500">*</span></label>
                            <select id="program_id" name="program_id" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none appearance-none" required>
                                <option value="">Select Program</option>
                                @foreach(\$programs as \$program)
                                    <option value="{{ \$program->id }}" {{ old('program_id') == \$program->id ? 'selected' : '' }}>{{ \$program->name }} ({{ \$program->code }})</option>
                                @endforeach
                            </select>
                            @error('program_id') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ \$message }}</p> @enderror
                        </div>
                    </div>
EOD;

$new = <<<EOD
                        <div>
                            <label for="program_id" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Program <span class="text-rose-500">*</span></label>
                            <select id="program_id" name="program_id" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none appearance-none" required>
                                <option value="">Select Program</option>
                                @foreach(\$programs as \$program)
                                    <option value="{{ \$program->id }}" {{ old('program_id') == \$program->id ? 'selected' : '' }}>{{ \$program->name }} ({{ \$program->code }})</option>
                                @endforeach
                            </select>
                            @error('program_id') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ \$message }}</p> @enderror
                        </div>

                        <div>
                            <label for="gender" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Gender <span class="text-rose-500">*</span></label>
                            <select id="gender" name="gender" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none appearance-none" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('Female') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ \$message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone_number" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Phone Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="+234..." required>
                            @error('phone_number') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ \$message }}</p> @enderror
                        </div>

                        <div>
                            <label for="nationality" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Nationality <span class="text-rose-500">*</span></label>
                            <input type="text" name="nationality" id="nationality" value="{{ old('nationality') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="E.g. Nigerian" required>
                            @error('nationality') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ \$message }}</p> @enderror
                        </div>

                        <div>
                            <label for="place_of_work" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Place of Work</label>
                            <input type="text" name="place_of_work" id="place_of_work" value="{{ old('place_of_work') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="Where do they currently work?">
                            @error('place_of_work') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ \$message }}</p> @enderror
                        </div>
                    </div>
EOD;

$content = str_replace($old, $new, $content);
file_put_contents('resources/views/admin/cohorts/register_students.blade.php', $content);
echo "Patched admin single form!\n";
