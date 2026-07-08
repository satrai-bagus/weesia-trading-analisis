import { animate, scroll, stagger } from 'motion';

// Dramatic-but-honest landing motion: hero entrance springs and scroll-linked
// parallax. Falls back to static (fully visible) markup when the user prefers
// reduced motion or when the current page has no landing hooks.
export function setupLandingMotion() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    setupHeroEntrance();
    setupParallax();
}

function setupHeroEntrance() {
    const stages = Array.from(document.querySelectorAll('[data-hero-stage]'));

    if (!stages.length) {
        return;
    }

    stages.forEach((element) => {
        element.style.opacity = '0';
        element.style.willChange = 'transform, opacity';
    });

    try {
        animate(
            stages,
            { opacity: [0, 1], y: [36, 0] },
            {
                delay: stagger(0.14, { startDelay: 0.1 }),
                type: 'spring',
                stiffness: 80,
                damping: 16,
            },
        );
    } catch (error) {
        // Never leave the hero invisible if the animation engine fails.
        stages.forEach((element) => {
            element.style.opacity = '';
            element.style.transform = '';
        });
    }
}

function setupParallax() {
    document.querySelectorAll('[data-parallax]').forEach((element) => {
        const speed = Number.parseFloat(element.dataset.parallax || '0');

        if (!speed) {
            return;
        }

        const distance = Math.max(-320, Math.min(320, speed * 320));
        const target = element.closest('section') || element;

        try {
            scroll(animate(element, { y: [distance, -distance] }, { ease: 'linear' }), {
                target,
                offset: ['start end', 'end start'],
            });
        } catch (error) {
            // Parallax is decoration only - ignore engine differences quietly.
        }
    });
}
