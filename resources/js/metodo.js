/**
 * /metodo — Alpine factory para la página long-form editorial v2.
 *
 * Spec: prompt-implementacion-blade.md §6 fase E (Interactividad Alpine)
 *
 * Responsabilidades:
 *   - Tracking del capítulo activo via IntersectionObserver (sidebar nav + chapter pill).
 *   - Barra de progreso de scroll (% leído) en el sidebar.
 *   - Visibilidad del sticky-mobile-cta (aparece tras 60% de scroll).
 *   - Scroll suave a chapter cuando se hace click en el sidebar nav.
 *   - Reveal de la curva SVG del Cap03 (stroke-dashoffset 900→0 + dots/labels).
 *
 * Respeta `prefers-reduced-motion: reduce` (CSS desactiva animaciones; este JS
 * todavía actualiza estado para mantener nav funcional sin animación).
 *
 * Cargado vía @push('scripts') o tag <script> al final del body en metodo.blade.php.
 */
window.metodoPage = function metodoPage() {
    return {
        activeChapter: 'cap-hero',
        activePill: '',
        scrollProgress: 0,
        stickyVisible: false,
        _scrollHandler: null,
        _chapterObserver: null,
        _svgObserver: null,

        init() {
            // ── 1. IntersectionObserver para chapters (sidebar nav + pill)
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
                    // El cap activo es el que está en el tercio superior del viewport.
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

            // ── 3. SVG curve reveal (Cap03)
            const svgFig = document.querySelector('.metodo-svg-figure');
            if (svgFig && 'IntersectionObserver' in window) {
                this._svgObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('curve-reveal-active');
                            this._svgObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.35 });
                this._svgObserver.observe(svgFig);
            }
        },

        // Smooth scroll a un capítulo del sidebar nav.
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
            if (this._svgObserver) {
                this._svgObserver.disconnect();
                this._svgObserver = null;
            }
        },
    };
};
