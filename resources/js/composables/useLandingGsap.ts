import { onMounted, onUnmounted, nextTick } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Register GSAP plugins safely
if (typeof window !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
}

export function useLandingGsap() {
    let ctx: gsap.Context | null = null;

    const setupGsap = (callback: (context: gsap.Context) => void) => {
        onMounted(async () => {
            await nextTick();
            // Wrap in gsap.context for full cleanup on component unmount (Inertia SPA safety)
            ctx = gsap.context(() => {
                callback(ctx!);
            });
            ScrollTrigger.refresh();
        });

        onUnmounted(() => {
            if (ctx) {
                ctx.revert(); // Automatically kills all GSAP animations and ScrollTriggers inside context
                ctx = null;
            }
        });
    };

    return {
        gsap,
        ScrollTrigger,
        setupGsap,
    };
}
