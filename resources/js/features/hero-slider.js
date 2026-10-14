export function heroSlider(slides = []) {
    return {
        slides: slides,
        current: 0,
        timer: null,
        autoplayInterval: 5000,
        paused: false,

        init() {
            if (this.slides.length > 1) {
                this.startAutoplay();
            }
        },

        startAutoplay() {
            this.stopAutoplay();
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            this.timer = setInterval(() => {
                if (!this.paused) {
                    this.next();
                }
            }, this.autoplayInterval);
        },

        stopAutoplay() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        next() {
            if (this.slides.length === 0) return;
            this.current = (this.current + 1) % this.slides.length;
        },

        prev() {
            if (this.slides.length === 0) return;
            this.current = (this.current - 1 + this.slides.length) % this.slides.length;
        },

        goTo(index) {
            if (index >= 0 && index < this.slides.length) {
                this.current = index;
            }
        },

        pause() {
            this.paused = true;
        },

        resume() {
            this.paused = false;
        },

        destroy() {
            this.stopAutoplay();
        },
    };
}
