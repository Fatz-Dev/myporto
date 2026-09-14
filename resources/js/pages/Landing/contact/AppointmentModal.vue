<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const isSuccess = ref(false);

const form = useForm({
    full_name: '',
    email: '',
    phone: '',
    service_type: 'Full Stack Development',
    preferred_date: '',
    notes: '',
});

const submitBooking = () => {
    form.post('/consultation', {
        preserveScroll: true,
        onSuccess: () => {
            isSuccess.value = true;
            form.reset();
            setTimeout(() => {
                isSuccess.value = false;
                emit('close');
            }, 3000);
        },
    });
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <!-- Modal Dialog -->
        <div class="relative w-full max-w-lg rounded-3xl bg-[#11141d] border border-white/[0.12] p-6 sm:p-8 shadow-2xl z-10 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.08]">
                <div>
                    <h3 class="text-xl font-bold text-white tracking-tight">Book a Discovery Call</h3>
                    <p class="text-xs text-slate-400 mt-0.5">30-minute consultation regarding your project.</p>
                </div>
                <button
                    type="button"
                    class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/[0.06] transition-colors"
                    @click="emit('close')"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Success State Message -->
            <div v-if="isSuccess" class="text-center py-8 space-y-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto bg-emerald-500/10 border border-emerald-500/20">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h4 class="text-lg font-bold text-white">Booking confirmed!</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
                    Thank you! I will review the details and reach out within 24 hours to confirm our call session.
                </p>
            </div>

            <!-- Form -->
            <form v-else class="space-y-4" @submit.prevent="submitBooking">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Your Full Name</label>
                    <input
                        v-model="form.full_name"
                        type="text"
                        required
                        placeholder="John Doe"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736]"
                    />
                    <span v-if="form.errors.full_name" class="text-xs text-rose-400 mt-1 block">{{ form.errors.full_name }}</span>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Email Address</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="you@company.com"
                            class="w-full px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736]"
                        />
                        <span v-if="form.errors.email" class="text-xs text-rose-400 mt-1 block">{{ form.errors.email }}</span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Phone / WhatsApp</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            required
                            placeholder="+1 (555) 000-0000"
                            class="w-full px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736]"
                        />
                        <span v-if="form.errors.phone" class="text-xs text-rose-400 mt-1 block">{{ form.errors.phone }}</span>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Project / Service Type</label>
                        <select
                            v-model="form.service_type"
                            class="w-full px-4 py-2.5 rounded-xl bg-[#11141d] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736]"
                        >
                            <option value="Full Stack Development">Full Stack Development</option>
                            <option value="Frontend Only">Frontend Only</option>
                            <option value="Backend / API">Backend / API</option>
                            <option value="Consulting / Audit">Consulting / Audit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Preferred Date</label>
                        <input
                            v-model="form.preferred_date"
                            type="date"
                            required
                            class="w-full px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736]"
                        />
                        <span v-if="form.errors.preferred_date" class="text-xs text-rose-400 mt-1 block">{{ form.errors.preferred_date }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">Project Overview / Notes (Optional)</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        placeholder="Briefly describe what you'd like to discuss..."
                        class="w-full px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm resize-none focus:outline-none focus:border-[#d97736] focus:ring-1 focus:ring-[#d97736]"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full inline-flex items-center justify-center gap-2 bg-[#d97736] hover:bg-[#e58546] text-[#0a0c10] font-bold text-xs tracking-wider uppercase rounded-xl py-3 px-6 shadow-[0_4px_16px_rgba(217,119,54,0.25)] active:scale-[0.98] transition-all disabled:opacity-50"
                >
                    <span v-if="form.processing">Processing booking...</span>
                    <span v-else>Confirm Booking &rarr;</span>
                </button>
            </form>
        </div>
    </div>
</template>
