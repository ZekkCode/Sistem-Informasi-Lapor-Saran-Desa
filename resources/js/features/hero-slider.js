/**
 * Slider foto Desa Padelegan:
 * 1. initHeroBgSlider: background atmosferik halus di hero beranda tanpa tombol navigasi.
 * 2. initVillageShowcase: slider showcase foto desa interaktif sebelum footer lengkap dengan tombol navigasi kiri/kanan dan indikator.
 */

export function initHeroBgSlider() {
    const container = document.querySelector('[data-hero-bg-slider]');
    if (!container) return;

    const slides = Array.from(container.querySelectorAll('[data-hero-bg-slide]'));
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
                slide.classList.add('opacity-25', 'z-10');
            } else {
                slide.classList.remove('opacity-25', 'z-10');
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
        }, 6000);
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

export function initVillageShowcase() {
    const showcase = document.querySelector('[data-village-showcase]');
    if (!showcase) return;

    const slides = Array.from(showcase.querySelectorAll('[data-showcase-slide]'));
    if (slides.length === 0) return;

    const prevBtn = showcase.querySelector('[data-showcase-prev]');
    const nextBtn = showcase.querySelector('[data-showcase-next]');
    const dots = Array.from(showcase.querySelectorAll('[data-showcase-dot]'));
    const counter = showcase.querySelector('[data-showcase-counter]');
    const captionEl = showcase.querySelector('[data-showcase-caption]');
    const locationEl = showcase.querySelector('[data-showcase-location]');
    const descriptionEl = showcase.querySelector('[data-showcase-description]');

    let currentIndex = 0;
    let timer = null;
    let isPaused = false;
    const interval = 5500;

    const updateUI = (index) => {
        // Update slides visibility
        slides.forEach((slide, idx) => {
            if (idx === index) {
                slide.classList.remove('opacity-0', 'pointer-events-none');
                slide.classList.add('opacity-100', 'z-10');
            } else {
                slide.classList.remove('opacity-100', 'z-10');
                slide.classList.add('opacity-0', 'pointer-events-none');
            }
        });

        // Update dots
        dots.forEach((dot, idx) => {
            if (idx === index) {
                dot.classList.remove('w-2', 'bg-white/40');
                dot.classList.add('w-7', 'bg-emerald-400');
                dot.setAttribute('aria-selected', 'true');
            } else {
                dot.classList.remove('w-7', 'bg-emerald-400');
                dot.classList.add('w-2', 'bg-white/40');
                dot.setAttribute('aria-selected', 'false');
            }
        });

        // Update active slide metadata
        const activeSlide = slides[index];
        if (activeSlide) {
            const title = activeSlide.getAttribute('data-caption') || '';
            const loc = activeSlide.getAttribute('data-location') || '';
            const desc = activeSlide.getAttribute('data-description') || '';

            if (captionEl) captionEl.textContent = title;
            if (locationEl) locationEl.textContent = loc;
            if (descriptionEl) descriptionEl.textContent = desc;
        }

        // Update counter
        if (counter) {
            counter.textContent = `${index + 1} / ${slides.length}`;
        }

        currentIndex = index;
    };

    const goTo = (index) => {
        if (index < 0) {
            index = slides.length - 1;
        } else if (index >= slides.length) {
            index = 0;
        }
        updateUI(index);
    };

    const next = () => goTo(currentIndex + 1);
    const prev = () => goTo(currentIndex - 1);

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prev();
            restartTimer();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            next();
            restartTimer();
        });
    }

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            goTo(idx);
            restartTimer();
        });
    });

    // Keyboard support when showcase is focused
    showcase.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            prev();
            restartTimer();
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            next();
            restartTimer();
        }
    });

    // Touch swipe support on mobile
    let touchStartX = 0;
    let touchEndX = 0;

    showcase.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        isPaused = true;
    }, { passive: true });

    showcase.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        isPaused = false;
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 40) {
            if (diff > 0) {
                prev();
            } else {
                next();
            }
            restartTimer();
        }
    }, { passive: true });

    // Autoplay
    const startTimer = () => {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }
        stopTimer();
        timer = setInterval(() => {
            if (!isPaused && document.visibilityState !== 'hidden') {
                next();
            }
        }, interval);
    };

    const stopTimer = () => {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    };

    const restartTimer = () => {
        stopTimer();
        startTimer();
    };

    showcase.addEventListener('mouseenter', () => { isPaused = true; });
    showcase.addEventListener('mouseleave', () => { isPaused = false; });
    showcase.addEventListener('focusin', () => { isPaused = true; });
    showcase.addEventListener('focusout', () => { isPaused = false; });

    document.addEventListener('visibilitychange', () => {
        isPaused = document.visibilityState === 'hidden';
    });

    // Inisialisasi slide pertama
    updateUI(0);
    startTimer();
}

export function heroSlider(slides = []) {
    return {
        slides,
        current: 0,
        init() {
            initHeroBgSlider();
            initVillageShowcase();
        },
    };
}
