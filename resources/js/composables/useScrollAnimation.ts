import { onMounted, onUnmounted, ref, type Ref } from 'vue';

interface UseScrollAnimationOptions {
    threshold?: number;
    rootMargin?: string;
    once?: boolean;
}

export function useScrollAnimation(options: UseScrollAnimationOptions = {}): {
    elementRef: Ref<HTMLElement | null>;
    isVisible: Ref<boolean>;
} {
    const {
        threshold = 0.1,
        rootMargin = '0px 0px -50px 0px',
        once = true,
    } = options;

    const elementRef = ref<HTMLElement | null>(null);
    const isVisible = ref(false);
    let observer: IntersectionObserver | null = null;

    onMounted(() => {
        if (!elementRef.value) return;

        // Check for reduced motion preference
        const prefersReducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        if (prefersReducedMotion) {
            isVisible.value = true;
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        isVisible.value = true;
                        if (once && observer) {
                            observer.unobserve(entry.target);
                        }
                    } else if (!once) {
                        isVisible.value = false;
                    }
                });
            },
            {
                threshold,
                rootMargin,
            },
        );

        observer.observe(elementRef.value);
    });

    onUnmounted(() => {
        if (observer) {
            observer.disconnect();
        }
    });

    return {
        elementRef,
        isVisible,
    };
}

export function useStaggeredAnimation(
    itemCount: number,
    baseDelay: number = 100,
): {
    getDelay: (index: number) => string;
} {
    const getDelay = (index: number): string => {
        return `${index * baseDelay}ms`;
    };

    return { getDelay };
}
