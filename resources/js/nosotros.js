/**
 * /nosotros — Alpine factory para la página brand storytelling editorial v2.
 *
 * Spec: 05-nosotros/redesigned-mobile.html + prompt-implementacion-blade.md
 *
 * Pattern espejo de window.procesoPage() / window.metodoPage().
 *
 * Responsabilidades:
 *   - Tracking del capítulo activo via IntersectionObserver (sidebar nav + chapter pill mobile).
 *   - Barra de progreso de scroll (% leído) en el sidebar.
 *   - Scroll suave a capítulo cuando se hace click en el sidebar nav.
 *   - Reveal de timeline items + value pull-quotes al entrar viewport.
 *
 * Respeta `prefers-reduced-motion: reduce` (CSS desactiva animaciones).
 *
 * Cargado vía resources/js/alpine-public.js antes de Alpine.start().
 */
window.nosotrosPage = function nosotrosPage() {
    return {
        activeChapter: 'cap-hero',
        activePill: '',
        scrollProgress: 0,
        _scrollHandler: null,
        _chapterObserver: null,
        _revealObserver: null,

        init() {
            // ── 1. IntersectionObserver para capítulos (sidebar nav + pill)
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

            // ── 2. Scroll progress (sidebar bar)
            this._scrollHandler = () => {
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const pct = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;
                this.scrollProgress = Math.min(100, Math.max(0, pct));
            };
            window.addEventListener('scroll', this._scrollHandler, { passive: true });
            this._scrollHandler();

            // ── 3. Reveal-on-scroll para timeline items + pull-quote values
            const revealTargets = document.querySelectorAll('[data-nosotros-reveal]');
            if (revealTargets.length > 0 && 'IntersectionObserver' in window) {
                this._revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('nosotros-revealed');
                            this._revealObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.18 });
                revealTargets.forEach((el) => this._revealObserver.observe(el));
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
            if (this._revealObserver) {
                this._revealObserver.disconnect();
                this._revealObserver = null;
            }
        },
    };
};
