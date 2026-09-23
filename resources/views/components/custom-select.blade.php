@props(['options' => [], 'placeholder' => 'Select an option'])

<div
    x-data="{
        open: false,
        value: $wire.entangle('{{ $attributes->wire('model')->value() }}'),
        options: {{ json_encode($options) }},
        get selectedLabel() {
            let selected = this.options.find(opt => opt.value == this.value);
            return selected ? selected.label : '{{ $placeholder }}';
        },
        selectOption(val) {
            this.value = val;
            this.open = false;
        }
    }"
    class="relative w-full"
    @click.away="open = false"
>
    <!-- Select Button -->
    <button 
        type="button"
        @click="open = !open"
        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors"
        :class="{ 'border-indigo-500 ring-1 ring-indigo-500': open }"
    >
        <span class="block truncate" x-text="selectedLabel" :class="{ 'text-gray-500': !value, 'text-gray-900': value }"></span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div 
        x-show="open" 
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
        style="display: none;"
    >
        <ul class="pt-1">
            <template x-for="option in options" :key="option.value">
                <li 
                    class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 transition-colors"
                    @click="selectOption(option.value)"
                >
                    <span class="block truncate" :class="{ 'font-semibold': value == option.value, 'font-normal': value != option.value }" x-text="option.label"></span>
                    
                    <span 
                        x-show="value == option.value" 
                        class="text-indigo-600 absolute inset-y-0 right-0 flex items-center pr-4"
                    >
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            </template>
        </ul>
    </div>
</div>
