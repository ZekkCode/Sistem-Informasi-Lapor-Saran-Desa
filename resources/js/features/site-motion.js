const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const precisePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

async function initAos() {
    if (!document.querySelector('[data-aos]')) {
        return;
    }

    const { default: AOS } = await import('aos');

    AOS.init({
        duration: 550,
        easing: 'ease-out-cubic',
        once: true,
        mirror: false,
        offset: 24,
        debounceDelay: 75,
        throttleDelay: 100,
    });
}

function initLenis() {
    if (reducedMotion.matches || !precisePointer.matches) {
        return;
    }

    const start = async () => {
        const { default: Lenis } = await import('lenis');

        new Lenis({
            autoRaf: true,
            anchors: {
                offset: -72,
            },
            lerp: 0.12,
            smoothWheel: true,
            syncTouch: false,
            overscroll: true,
        });
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(start, { timeout: 1000 });
        return;
    }

    window.setTimeout(start, 200);
}

export function initSiteMotion() {
    if (document.body.classList.contains('admin-body')) {
        return;
    }

    initAos();
    initLenis();
}
