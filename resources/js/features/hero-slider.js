/**
 * Slider latar belakang hero beranda tanpa tombol navigasi.
 * Menjalankan transisi cross-fade foto desa secara otomatis.
 */
export function initHeroBgSlider() {
    const container = document.querySelector('[data-hero-bg-slider]');
    if (!container) return;

    const slides = Array.from(container.querySelectorAll('[data-slide-index]'));
    if (slides.length <= 1) return;

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    let currentIndex = 0;
    let timer = null;
    let isPaused = false;

    const showSlide = (nextIndex) => {
        slides.forEach((slide, idx) => {
            if (idx === nextIndex) {
                slide.classList.remove('opacity-0', 'pointer-events-none');
                slide.classList.add('opacity-100', 'z-10');
            } else {
                slide.classList.remove('opacity-100', 'z-10');
                slide.classList.add('opacity-0', 'pointer-events-none');
            }
        });
        currentIndex = nextIndex;
    };

    const nextSlide = () => {
        const next = (currentIndex + 1) % slides.length;
        showSlide(next);
    };

    const start = () => {
        stop();
        timer = setInterval(() => {
            if (!isPaused && document.visibilityState !== 'hidden') {
                nextSlide();
            }
        }, 5000);
    };

    const stop = () => {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    };

    const heroSection = container.closest('.home-hero') || container;
    heroSection.addEventListener('mouseenter', () => { isPaused = true; });
    heroSection.addEventListener('mouseleave', () => { isPaused = false; });
    heroSection.addEventListener('focusin', () => { isPaused = true; });
    heroSection.addEventListener('focusout', () => { isPaused = false; });

    document.addEventListener('visibilitychange', () => {
        isPaused = document.visibilityState === 'hidden';
    });

    start();
}

export function heroSlider(slides = []) {
    return {
        slides,
        current: 0,
        init() {
            initHeroBgSlider();
        },
    };
}
