<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useLandingGsap } from '@/composables/useLandingGsap';

const props = defineProps<{
    profile?: any;
    flash?: any;
}>();

const emit = defineEmits<{
    (e: 'open-appointment'): void;
}>();

const { gsap, setupGsap } = useLandingGsap();

setupGsap(() => {
    if (document.querySelector('.contact-header-anim')) {
        gsap.fromTo('.contact-header-anim',
            { opacity: 0, y: 30 },
            {
                scrollTrigger: {
                    trigger: '.contact-header-anim',
                    start: 'top 85%',
                },
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
    if (document.querySelector('.contact-info-anim')) {
        gsap.fromTo('.contact-info-anim',
            { opacity: 0, x: -25 },
            {
                scrollTrigger: {
                    trigger: '.contact-info-anim',
                    start: 'top 85%',
                },
                opacity: 1,
                x: 0,
                duration: 0.8,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
    if (document.querySelector('.contact-form-anim')) {
        gsap.fromTo('.contact-form-anim',
            { opacity: 0, x: 25 },
            {
                scrollTrigger: {
                    trigger: '.contact-form-anim',
                    start: 'top 85%',
                },
                opacity: 1,
                x: 0,
                duration: 0.8,
                ease: 'power2.out',
                clearProps: 'all',
            }
        );
    }
});

const formSuccess = ref(false);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submitContact = () => {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            formSuccess.value = true;
            form.reset();
            setTimeout(() => {
                formSuccess.value = false;
            }, 5000);
        },
    });
};
</script>

<template>
    <section id="contact" class="px-6 sm:px-10 lg:px-14 py-28 border-t border-white/[0.08]">
        <div class="max-w-6xl mx-auto space-y-16">
            <!-- Section Header -->
            <div class="contact-header-anim space-y-3">
                <p class="font-mono text-xs text-[#d97736] tracking-[0.2em] uppercase font-semibold">// Get in touch</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                    Let's start <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400">something great.</span>
                </h2>
                <p class="text-slate-400 text-sm sm:text-base max-w-xl leading-relaxed">
                    Whether you have a clear brief or just an idea - I'm happy to talk it through. No hard sell, just honest conversation.
                </p>
            </div>

            <!-- Contact Grid Layout -->
            <div class="grid lg:grid-cols-12 gap-12">
                <!-- Left Info Column -->
                <div class="contact-info-anim lg:col-span-5 space-y-6">
                    <!-- Direct Line Card -->
                    <div class="p-6 rounded-2xl bg-white/[0.02] border border-white/[0.08] space-y-3">
                        <span class="text-[10px] font-mono font-semibold uppercase tracking-widest text-[#d97736]">Direct line</span>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#d97736]/10 border border-[#d97736]/20 flex items-center justify-center text-[#d97736]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <a :href="'tel:' + (profile?.phone || '+19075550101')" class="text-base font-bold text-white hover:text-[#d97736] font-mono transition-colors">
                                    {{ profile?.phone || '+1 (907) 555-0101' }}
                                </a>
                                <p class="text-xs text-slate-400">Monday - Friday &middot; 9AM - 6PM GMT</p>
                            </div>
                        </div>
                    </div>

                    <!-- Email Contact Card -->
                    <div class="p-6 rounded-2xl bg-white/[0.02] border border-white/[0.08] space-y-3">
                        <span class="text-[10px] font-mono font-semibold uppercase tracking-widest text-[#d97736]">Email Inbox</span>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#d97736]/10 border border-[#d97736]/20 flex items-center justify-center text-[#d97736]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <a :href="'mailto:' + (profile?.email || 'hello@fatzdev.com')" class="text-base font-bold text-white hover:text-[#d97736] font-mono transition-colors">
                                    {{ profile?.email || 'hello@fatzdev.com' }}
                                </a>
                                <p class="text-xs text-slate-400">Response guaranteed within 24 hours</p>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Right Form Column -->
                <div class="contact-form-anim lg:col-span-7">
                    <div class="rounded-3xl bg-[#11141d] border border-white/[0.08] p-6 sm:p-10 space-y-6">
                        <!-- Success Banner -->
                        <div
                            v-if="formSuccess || flash?.success"
                            class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-3"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Message sent! I'll be in touch within 24 hours.</span>
                        </div>

                        <form class="space-y-5" @submit.prevent="submitContact">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Full name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Your name"
                                    class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736] transition-all"
                                />
                                <span v-if="form.errors.name" class="text-xs text-rose-400 mt-1 block">{{ form.errors.name }}</span>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Email address</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        placeholder="you@company.com"
                                        class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736] transition-all"
                                    />
                                    <span v-if="form.errors.email" class="text-xs text-rose-400 mt-1 block">{{ form.errors.email }}</span>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Subject</label>
                                    <input
                                        v-model="form.subject"
                                        type="text"
                                        required
                                        placeholder="What's this about?"
                                        class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736] transition-all"
                                    />
                                    <span v-if="form.errors.subject" class="text-xs text-rose-400 mt-1 block">{{ form.errors.subject }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Message</label>
                                <textarea
                                    v-model="form.message"
                                    rows="5"
                                    required
                                    placeholder="Tell me about your project, timeline, and goals..."
                                    class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm resize-none focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736] transition-all"
                                ></textarea>
                                <span v-if="form.errors.message" class="text-xs text-rose-400 mt-1 block">{{ form.errors.message }}</span>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full inline-flex items-center justify-center gap-2.5 bg-[#d97736] hover:bg-[#e58546] text-[#0a0c10] font-bold text-xs tracking-wider uppercase rounded-xl py-3.5 px-6 shadow-[0_4px_16px_rgba(217,119,54,0.22)] active:scale-[0.98] transition-all disabled:opacity-50"
                            >
                                <span v-if="form.processing">Sending message...</span>
                                <span v-else>Send message &rarr;</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
