// Coach Dashboard — Alpine stores + counter animations + swipe gestures

document.addEventListener('alpine:init', () => {
    Alpine.store('coachSidebar', {
        collapsed: localStorage.getItem('coachSidebarCollapsed') === 'true',
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('coachSidebarCollapsed', String(this.collapsed));
        }
    });
});

// Counter animation 0 → N for stat cards
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-counter]').forEach((el, i) => {
        const target = parseInt(el.dataset.counter, 10);
        if (!target) return;
        let current = 0;
        const step = Math.ceil(target / 20);
        setTimeout(() => {
            const interval = setInterval(() => {
                current = Math.min(current + step, target);
                el.textContent = current;
                if (current >= target) clearInterval(interval);
            }, 40);
        }, i * 100);
    });

    // Swipe-to-reveal handlers for urgent client cards
    document.querySelectorAll('.swipe-item').forEach(item => {
        let startX = 0;
        item.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
        item.addEventListener('touchmove', e => {
            const dx = e.touches[0].clientX - startX;
            if (Math.abs(dx) > 10) item.style.transform = `translateX(${Math.max(-80, Math.min(0, dx))}px)`;
        }, { passive: true });
        item.addEventListener('touchend', () => {
            const current = parseInt(item.style.transform.replace('translateX(', '')) || 0;
            item.style.transform = current < -40 ? 'translateX(-80px)' : 'translateX(0)';
        });
    });
});
