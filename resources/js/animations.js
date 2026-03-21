/**
 * WellCore Scroll Animations
 * Uses IntersectionObserver to animate elements with data-animate attribute
 */
document.addEventListener('DOMContentLoaded', () => {
    // Respect reduced motion preference
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('[data-animate]').forEach(el => {
            el.classList.add('animate-in');
        });
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px',
        }
    );

    document.querySelectorAll('[data-animate]').forEach((el) => {
        observer.observe(el);
    });
});

// === Animated Number Counter ===
function animateCounter(el, target, duration = 1500) {
    let start = 0;
    const suffix = el.dataset.counterSuffix || '';
    const prefix = el.dataset.counterPrefix || '';
    const step = (timestamp) => {
        if (!start) start = timestamp;
        const progress = Math.min((timestamp - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
        el.textContent = prefix + Math.floor(eased * target).toLocaleString() + suffix;
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = prefix + target.toLocaleString() + suffix;
    };
    requestAnimationFrame(step);
}

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const target = parseInt(entry.target.dataset.counter);
            if (!isNaN(target)) {
                animateCounter(entry.target, target);
            }
            counterObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.3 });

document.querySelectorAll('[data-counter]').forEach(el => counterObserver.observe(el));

// Re-init after Livewire navigation (wire:navigate)
document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('[data-counter]').forEach(el => {
        if (!el.dataset.counterAnimated) {
            counterObserver.observe(el);
        }
    });
    // Re-init parallax after Livewire navigation
    initParallaxHero();
});

// === Subtle Parallax for Hero Sections ===
function initParallaxHero() {
    // Bail out if reduced motion is preferred
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    // Detect mobile/touch devices — disable parallax (perf + iOS issues)
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)
        || (navigator.maxTouchPoints > 0 && window.innerWidth < 1024);
    if (isMobile) return;

    const heroSections = document.querySelectorAll('.parallax-hero');
    if (heroSections.length === 0) return;

    let ticking = false;

    function updateParallax() {
        const scrollY = window.scrollY;
        heroSections.forEach(hero => {
            const section = hero.parentElement;
            if (!section) return;
            const rect = section.getBoundingClientRect();
            const sectionBottom = rect.bottom;
            // Only animate while section is visible (above fold + a bit)
            if (sectionBottom < 0 || rect.top > window.innerHeight) return;

            const orbs = hero.querySelectorAll('.parallax-orb');
            orbs.forEach(orb => {
                const speed = parseFloat(orb.dataset.parallaxSpeed || '0.3');
                const yOffset = scrollY * speed;
                // Cap the movement to prevent excessive displacement
                const maxOffset = 150;
                const clampedOffset = Math.min(yOffset, maxOffset);
                orb.style.transform = `translateY(${clampedOffset}px)`;
            });
        });
        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }, { passive: true });
}

// Init parallax on first load
document.addEventListener('DOMContentLoaded', initParallaxHero);
