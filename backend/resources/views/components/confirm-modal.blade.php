<div x-data="{
        isOpen: false,
        title: 'Please Confirm',
        message: 'Are you sure you want to proceed?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'danger', // 'danger', 'warning', 'success', 'info'
        resolve: null,

        open(data) {
            this.title = data.title || 'Please Confirm';
            this.message = data.message || 'Are you sure you want to proceed?';
            this.type = data.type || 'danger';
            this.confirmText = data.confirmText || (this.type === 'danger' ? 'Confirm' : 'Yes, Proceed');
            this.cancelText = data.cancelText || 'Cancel';
            this.resolve = data.resolve || (() => {});
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        confirm() {
            if (this.resolve) this.resolve(true);
            this.close();
        },

        cancel() {
            if (this.resolve) this.resolve(false);
            this.close();
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
    @open-confirm-modal.window="open($event.detail)"
    @keydown.escape.window="cancel()"
    x-show="isOpen"
    style="display: none;"
    class="fixed inset-0 z-[200] overflow-y-auto"
    aria-labelledby="confirm-modal-title"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop Blur Overlay -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity"
         @click="cancel()"
    ></div>

    <!-- Modal Dialog Center -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100"
             @click.stop
        >
            <!-- Top Ambient Glow -->
            <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full blur-3xl opacity-15 pointer-events-none"
                 :class="{
                    'bg-rose-500': type === 'danger',
                    'bg-amber-500': type === 'warning',
                    'bg-emerald-500': type === 'success',
                    'bg-indigo-500': type === 'info'
                 }">
            </div>

            <div class="p-8 sm:p-10 relative z-10">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
                    <!-- Icon Badge with Soft Glow -->
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 shadow-sm border transition-all duration-300"
                         :class="{
                            'bg-rose-50 text-rose-600 border-rose-100 ring-8 ring-rose-50/50': type === 'danger',
                            'bg-amber-50 text-amber-600 border-amber-100 ring-8 ring-amber-50/50': type === 'warning',
                            'bg-emerald-50 text-emerald-600 border-emerald-100 ring-8 ring-emerald-50/50': type === 'success',
                            'bg-indigo-50 text-indigo-600 border-indigo-100 ring-8 ring-indigo-50/50': type === 'info'
                         }">
                        <!-- Danger Icon (X / Warning) -->
                        <template x-if="type === 'danger'">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </template>
                        <!-- Warning Icon -->
                        <template x-if="type === 'warning'">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                        <!-- Success Icon -->
                        <template x-if="type === 'success'">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </template>
                        <!-- Info Icon -->
                        <template x-if="type === 'info'">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug" id="confirm-modal-title" x-text="title"></h3>
                        <p class="mt-2.5 text-sm font-medium text-slate-500 leading-relaxed" x-text="message"></p>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="bg-slate-50/80 px-8 py-5 sm:px-10 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
                <button type="button"
                        @click="cancel()"
                        class="w-full sm:w-auto px-6 py-3 bg-white border border-slate-200 rounded-xl text-xs font-black uppercase tracking-wider text-slate-800 hover:bg-slate-50 transition-colors shadow-sm"
                        x-text="cancelText"
                >
                    Cancel
                </button>
                <button type="button"
                        @click="confirm()"
                        class="w-full sm:w-auto px-7 py-3 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2"
                        :class="{
                            'bg-rose-500 hover:bg-rose-600 shadow-rose-500/25': type === 'danger',
                            'bg-amber-500 hover:bg-amber-600 shadow-amber-500/25': type === 'warning',
                            'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25': type === 'success',
                            'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25': type === 'info'
                        }"
                >
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.confirmModal = function(options) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    return new Promise((resolve) => {
        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
            detail: {
                ...options,
                resolve
            }
        }));
    });
};

if (!window.toast) {
    window.toast = {
        success: (msg) => window.dispatchEvent(new CustomEvent('notify', { detail: { message: msg, type: 'success' } })),
        error: (msg) => window.dispatchEvent(new CustomEvent('notify', { detail: { message: msg, type: 'error' } })),
        info: (msg) => window.dispatchEvent(new CustomEvent('notify', { detail: { message: msg, type: 'info' } })),
        warning: (msg) => window.dispatchEvent(new CustomEvent('notify', { detail: { message: msg, type: 'warning' } }))
    };
}

// Global alert replacement to prevent browser default alert popups
window.alert = function(msg) {
    if (!msg) return;
    const lower = String(msg).toLowerCase();
    const isError = lower.includes('error') || lower.includes('fail') || lower.includes('exceed') || lower.includes('warning') || lower.includes('cannot') || lower.includes('locked') || lower.includes('select');
    if (isError) {
        window.toast.error(msg);
    } else {
        window.toast.info(msg);
    }
};

// Global click & submit interceptor for [data-confirm]
document.addEventListener('click', async function(e) {
    const el = e.target.closest('[data-confirm]');
    if (!el || el._confirmed) {
        if (el) el._confirmed = false;
        return;
    }
    
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    const message = el.getAttribute('data-confirm');
    const title = el.getAttribute('data-confirm-title') || 'Please Confirm';
    const type = el.getAttribute('data-confirm-type') || (
        message.toLowerCase().includes('delete') || message.toLowerCase().includes('reject') || message.toLowerCase().includes('demote') || message.toLowerCase().includes('remove') ? 'danger' : 'success'
    );
    const confirmBtn = el.getAttribute('data-confirm-btn') || (
        type === 'danger' ? (message.toLowerCase().includes('delete') ? 'Delete' : (message.toLowerCase().includes('reject') ? 'Reject' : 'Confirm')) : 'Confirm'
    );
    const cancelBtn = el.getAttribute('data-confirm-cancel') || 'Cancel';
    
    const ok = await window.confirmModal({
        title,
        message,
        type,
        confirmText: confirmBtn,
        cancelText: cancelBtn
    });
    
    if (ok) {
        el._confirmed = true;
        if (el.tagName === 'FORM') {
            HTMLFormElement.prototype.submit.call(el);
            return;
        }
        if (el.tagName === 'A' && el.href) {
            window.location.href = el.href;
            return;
        }
        const form = el.closest('form');
        if (form && (el.type === 'submit' || !el.type)) {
            form._confirmed = true;
            HTMLFormElement.prototype.submit.call(form);
            return;
        }
        if (typeof el.click === 'function') {
            el.click();
        }
    }
}, true);

document.addEventListener('submit', async function(e) {
    const form = e.target;
    if (!form || !form.hasAttribute('data-confirm') || form._confirmed) {
        if (form) form._confirmed = false;
        return;
    }
    
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    const message = form.getAttribute('data-confirm');
    const title = form.getAttribute('data-confirm-title') || 'Please Confirm';
    const type = form.getAttribute('data-confirm-type') || (
        message.toLowerCase().includes('delete') || message.toLowerCase().includes('reject') || message.toLowerCase().includes('remove') ? 'danger' : 'success'
    );
    const confirmBtn = form.getAttribute('data-confirm-btn') || (
        type === 'danger' ? (message.toLowerCase().includes('delete') ? 'Delete' : (message.toLowerCase().includes('reject') ? 'Reject' : 'Confirm')) : 'Confirm'
    );
    const cancelBtn = form.getAttribute('data-confirm-cancel') || 'Cancel';
    
    const ok = await window.confirmModal({
        title,
        message,
        type,
        confirmText: confirmBtn,
        cancelText: cancelBtn
    });
    
    if (ok) {
        form._confirmed = true;
        HTMLFormElement.prototype.submit.call(form);
    }
}, true);
</script>
