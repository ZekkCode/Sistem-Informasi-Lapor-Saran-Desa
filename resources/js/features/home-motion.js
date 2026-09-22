const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const precisePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

function addHeroDepth() {
    if (reducedMotion.matches || !precisePointer.matches) {
        return;
    }

    const scene = document.querySelector('[data-depth-scene]');
    const plane = scene?.querySelector('[data-depth-plane]');

    if (!scene || !plane) {
        return;
    }

    let animationFrame;

    const updateDepth = (event) => {
        const bounds = scene.getBoundingClientRect();
        const x = (event.clientX - bounds.left) / bounds.width - 0.5;
        const y = (event.clientY - bounds.top) / bounds.height - 0.5;

        cancelAnimationFrame(animationFrame);
        animationFrame = requestAnimationFrame(() => {
            plane.style.setProperty('--depth-rx', `${(-y * 5).toFixed(2)}deg`);
            plane.style.setProperty('--depth-ry', `${(x * 5).toFixed(2)}deg`);
        });
    };

    const resetDepth = () => {
        cancelAnimationFrame(animationFrame);
        plane.style.setProperty('--depth-rx', '0deg');
        plane.style.setProperty('--depth-ry', '0deg');
    };

    scene.addEventListener('pointermove', updateDepth, { passive: true });
    scene.addEventListener('pointerleave', resetDepth, { passive: true });
}

export function initHomeMotion() {
    addHeroDepth();
}
