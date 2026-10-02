import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

# We need to move x-data from the inner div to the form
old_form = r"""                                            <form action="{{ route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex items-center gap-2 sm:ml-auto overflow-visible relative" @submit="if(selected.length === 0) { alert('Please select at least one examiner.'); $event.preventDefault(); }">
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
                                                }" class="relative w-48 z-50">"""

new_form = r"""                                            <form action="{{ route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex items-center gap-2 sm:ml-auto overflow-visible relative" 
                                                x-data="{
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
                                                }"
                                                @submit="if(selected.length === 0) { alert('Please select at least one examiner.'); $event.preventDefault(); }">
                                                @csrf
                                                <!-- Alpine component for multiselect -->
                                                <div class="relative w-48 z-50">"""

content = content.replace(old_form, new_form)

with open(path, 'w') as f:
    f.write(content)
