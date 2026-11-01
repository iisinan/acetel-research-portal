<div x-data="{ 
    isOpen: false, 
    documentUrl: '', 
    documentTitle: '', 
    documentType: '', // 'pdf', 'image', 'other'
    
    openModal(data) {
        this.documentUrl = data.url;
        this.documentTitle = data.title;
        this.documentType = data.type || this.detectType(data.url);
        this.isOpen = true;
        document.body.classList.add('overflow-hidden');
    },
    
    closeModal() {
        this.isOpen = false;
        setTimeout(() => {
            this.documentUrl = '';
            this.documentTitle = '';
            this.documentType = '';
            document.body.classList.remove('overflow-hidden');
        }, 300);
    },
    
    detectType(url) {
        if (!url) return 'other';
        const cleanUrl = url.split(/[?#]/)[0];
        const ext = cleanUrl.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return 'image';
        if (ext === 'pdf') return 'pdf';
        return 'other';
    },

    printDocument() {
        if (this.documentType === 'pdf') {
            const iframe = document.getElementById('document-preview-iframe');
            if (iframe) {
                iframe.contentWindow.print();
            }
        } else if (this.documentType === 'image') {
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Image</title></head><body style=\'margin:0;display:flex;justify-content:center;align-items:center;height:100vh;\'><img src=\'' + this.documentUrl + '\' style=\'max-width:100%;max-height:100%;\' /></body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
    }
}"
@open-document-preview.window="openModal($event.detail)"
@keydown.escape.window="closeModal()"
x-show="isOpen"
style="display: none;"
class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <!-- Background backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div x-show="isOpen" 
                 @click.away="closeModal()"
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all w-full max-w-5xl h-[85vh] flex flex-col border border-slate-100">
                
                <!-- Header -->
                <div class="bg-slate-50 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2 sm:pr-4">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-acetel-50 text-acetel-600 flex items-center justify-center shrink-0">
                            <!-- Document Icon -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs sm:text-sm font-black text-slate-900 truncate max-w-[150px] sm:max-w-md" id="modal-title" x-text="documentTitle || 'Document Preview'"></h3>
                            <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Secure Artifact Viewer</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                        <button type="button" @click="printDocument()" x-show="documentType === 'pdf' || documentType === 'image'" class="hidden sm:inline-flex p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-acetel-500 tooltip" title="Print Document">
                            <span class="sr-only">Print</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        </button>

                        <a :href="documentUrl" download class="p-2 text-slate-400 hover:text-acetel-600 hover:bg-acetel-50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-acetel-500 tooltip" title="Download Original File">
                            <span class="sr-only">Download</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        </a>

                        <a :href="documentUrl" target="_blank" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-acetel-500 tooltip" title="Open in New Tab">
                            <span class="sr-only">Open in New Tab</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>

                        <div class="h-6 w-px bg-slate-200 mx-1"></div>

                        <button type="button" @click="closeModal()" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <span class="sr-only">Close</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="flex-1 bg-slate-900 overflow-hidden relative group">
                    <template x-if="documentType === 'pdf'">
                        <iframe :src="documentUrl" id="document-preview-iframe" class="w-full h-full border-0 bg-white" title="PDF Preview"></iframe>
                    </template>
                    
                    <template x-if="documentType === 'image'">
                        <div class="w-full h-full flex items-center justify-center p-8">
                            <img :src="documentUrl" :alt="documentTitle" class="max-w-full max-h-full object-contain rounded-xl shadow-2xl">
                        </div>
                    </template>

                    <template x-if="documentType !== 'pdf' && documentType !== 'image'">
                        <div class="w-full h-full flex flex-col items-center justify-center p-12 text-center">
                            <div class="w-20 h-20 rounded-full bg-slate-800 flex items-center justify-center text-slate-500 mb-6 shadow-inner">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <h4 class="text-xl font-black text-white tracking-tight mb-2">Native Preview Unavailable</h4>
                            <p class="text-sm font-medium text-slate-400 max-w-sm mb-8">This file type (e.g. Word, Excel) cannot be rendered directly. Please download the original artifact to view its contents.</p>
                            <a :href="documentUrl" download class="inline-flex items-center px-6 py-3 bg-acetel-600 hover:bg-acetel-500 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-acetel-500/20 active:scale-95">
                                Download Original Artifact
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
