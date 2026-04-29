/**
 * /proceso — Alpine factory para la página long-form storytelling v2.
 *
 * Spec: 03-proceso/prompt-implementacion-blade.md §6 fase E
 *
 * Responsabilidades:
 *   - Tracking del paso activo via IntersectionObserver (sidebar nav + chapter pill).
 *   - Barra de progreso de scroll (% leído) en el sidebar.
 *   - Visibilidad del sticky-mobile-cta (aparece tras 60% de scroll).
 *   - Scroll suave a step cuando se hace click en el sidebar nav.
 *   - Reveal de las viz al entrar viewport: SVG coach rings + chart curve.
 *
 * Respeta `prefers-reduced-motion: reduce` (CSS desactiva animaciones; este JS
 * todavía actualiza estado para mantener nav funcional sin animación).
 *
 * Cargado vía resources/js/alpine-public.js antes de Alpine.start().
 */
window.procesoPage = function procesoPage() {
    return {
        activeChapter: 'cap-hero',
        activePill: '',
        scrollProgress: 0,
        stickyVisible: false,
        _scrollHandler: null,
        _chapterObserver: null,
        _vizObserver: null,

        init() {
            // ── 1. IntersectionObserver para steps (sidebar nav + pill)
            const sections = document.querySelectorAll('section[data-chapter]');
            if (sections.length > 0) {
                this._chapterObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const id = entry.target.id;
                            const label = entry.target.getAttribute('data-chapter-label') || '';
                            this.activeChapter = id;
                            this.activePill = label;
                        }
                    });
                }, {
                    rootMargin: '-30% 0px -50% 0px',
                    threshold: 0,
                });
                sections.forEach((s) => this._chapterObserver.observe(s));
            }

            // ── 2. Scroll progress + sticky CTA visibility
            this._scrollHandler = () => {
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const pct = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;
                this.scrollProgress = Math.min(100, Math.max(0, pct));
                this.stickyVisible = window.scrollY > window.innerHeight * 0.6;
            };
            window.addEventListener('scroll', this._scrollHandler, { passive: true });
            this._scrollHandler();

            // ── 3. Viz reveal al entrar viewport (rings + chart)
            const vizTargets = document.querySelectorAll('[data-proceso-viz]');
            if (vizTargets.length > 0 && 'IntersectionObserver' in window) {
                this._vizObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('proceso-viz-active');
                            this._vizObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.35 });
                vizTargets.forEach((el) => this._vizObserver.observe(el));
            }
        },

        scrollToChapter(id, event) {
            if (event) event.preventDefault();
            const target = document.getElementById(id);
            if (!target) return;
            const topbarH = parseInt(
                getComputedStyle(document.documentElement)
                    .getPropertyValue('--topbar-h') || '64',
                10,
            ) || 64;
            const top = target.getBoundingClientRect().top + window.scrollY - topbarH - 12;
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top, behavior: reduced ? 'auto' : 'smooth' });
        },

        destroy() {
            if (this._scrollHandler) {
                window.removeEventListener('scroll', this._scrollHandler);
                this._scrollHandler = null;
            }
            if (this._chapterObserver) {
                this._chapterObserver.disconnect();
                this._chapterObserver = null;
            }
            if (this._vizObserver) {
                this._vizObserver.disconnect();
                this._vizObserver = null;
            }
        },
    };
};
