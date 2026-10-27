import './bootstrap';
import './echo';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.store('sidebar', {
    // Desktop: default to open unless explicitly collapsed ('false')
    // Mobile: default to closed
    open: (function() {
        if (typeof window === 'undefined') return true;
        const isDesktop = window.innerWidth >= 768;
        if (!isDesktop) return false;
        const saved = localStorage.getItem('sidebar-desktop-open');
        return saved === null ? true : saved === 'true';
    })(),
    toggle() {
        this.open = !this.open;
        if (window.innerWidth >= 768) {
            localStorage.setItem('sidebar-desktop-open', this.open ? 'true' : 'false');
        }
    },
    close() {
        this.open = false;
        if (window.innerWidth >= 768) {
            localStorage.setItem('sidebar-desktop-open', 'false');
        }
    },
    openSidebar() {
        this.open = true;
        if (window.innerWidth >= 768) {
            localStorage.setItem('sidebar-desktop-open', 'true');
        }
    }
});

// Synchronize sidebar state on window resize across desktop/mobile breakpoint
if (typeof window !== 'undefined') {
    let lastWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        const currentWidth = window.innerWidth;
        if (lastWidth < 768 && currentWidth >= 768) {
            const saved = localStorage.getItem('sidebar-desktop-open');
            Alpine.store('sidebar').open = saved === null ? true : saved === 'true';
        } else if (lastWidth >= 768 && currentWidth < 768) {
            Alpine.store('sidebar').open = false;
        }
        lastWidth = currentWidth;
    });

    // Keyboard shortcuts: Ctrl+B or Cmd+B to toggle, Escape to close on mobile
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
            e.preventDefault();
            Alpine.store('sidebar').toggle();
        } else if (e.key === 'Escape' && Alpine.store('sidebar').open && window.innerWidth < 768) {
            Alpine.store('sidebar').close();
        }
    });
}

Alpine.store('darkMode', {
    on: localStorage.getItem('dark-mode') === 'true',
    toggle() {
        this.on = !this.on;
        localStorage.setItem('dark-mode', this.on);
        if (this.on) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
    init() {
        if (this.on) {
            document.documentElement.classList.add('dark');
        }
    }
});

Alpine.start();

// Real-time Inbox Listeners
const userIdMeta = document.querySelector('meta[name="user-id"]');
if (userIdMeta && window.Echo) {
    const userId = userIdMeta.getAttribute('content');
    window.Echo.private(`inbox.${userId}`)
        .listen('.MessageReceived', (e) => {
            console.log('Real-time message received:', e);
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: `New message from ${e.sender_name}`, type: 'info' }
            }));
            window.dispatchEvent(new CustomEvent('new-inbox-message', { detail: e }));
        })
        .listen('.MilestoneSubmitted', (e) => {
            console.log('Real-time milestone submitted:', e);
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: `Milestone Submitted: ${e.student_name} uploaded ${e.milestone_name}`, type: 'info' }
            }));
            window.dispatchEvent(new CustomEvent('milestone-submitted', { detail: e }));
        })
        .listen('.MilestoneApproved', (e) => {
            console.log('Milestone approved:', e);
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: e.message, type: 'success' }
            }));
            if (window.location.pathname.includes('/milestones')) {
                setTimeout(() => window.location.reload(), 2000);
            }
        })
        .listen('.MilestoneUpdated', (e) => {
            console.log('Milestone updated:', e);
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: e.message, type: 'info' }
            }));
            if (window.location.pathname.includes('/milestones')) {
                setTimeout(() => window.location.reload(), 2000);
            }
        })
        .listen('.EvaluationSubmitted', (e) => {
            console.log('Real-time evaluation submitted:', e);
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: `Evaluation Submitted for ${e.student_name}: ${e.recommendation}`, type: 'success' }
            }));
            window.dispatchEvent(new CustomEvent('evaluation-submitted', { detail: e }));
        });
}
