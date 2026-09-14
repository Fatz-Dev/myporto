<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = withDefaults(
    defineProps<{
        profile?: {
            full_name?: string;
            professional_title?: string;
            availability_badge_text?: string;
            location?: string;
            email?: string;
            phone?: string;
            primary_stack?: string[];
            hero_headline?: string;
            hero_subheadline?: string;
            bio_summary_1?: string;
            projects_delivered?: string;
            years_experience?: string;
        };
        embedded?: boolean;
    }>(),
    {
        embedded: false,
    }
);

const emit = defineEmits<{
    (e: 'open-appointment'): void;
}>();

// Interactive terminal history
interface TerminalEntry {
    id: number;
    command: string;
    outputType: 'whoami' | 'stack' | 'projects' | 'skills' | 'experience' | 'education' | 'contact' | 'ping' | 'help' | 'unknown';
    timestamp: string;
    customText?: string;
}

const history = ref<TerminalEntry[]>([
    {
        id: 1,
        command: 'whoami',
        outputType: 'whoami',
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    },
]);

const currentInput = ref('');
const terminalContainer = ref<HTMLElement | null>(null);
const commandInputRef = ref<HTMLInputElement | null>(null);
const historyIndex = ref<number>(-1);
const enteredCommands = ref<string[]>(['whoami']);

// List of available RUN preset commands
const presetCommands = [
    { label: 'whoami', cmd: 'whoami', icon: 'fa-solid fa-user' },
    { label: 'stack', cmd: 'stack', icon: 'fa-solid fa-layer-group' },
    { label: 'projects', cmd: 'projects', icon: 'fa-solid fa-folder-open' },
    { label: 'skills', cmd: 'skills', icon: 'fa-solid fa-code' },
    { label: 'experience', cmd: 'experience', icon: 'fa-solid fa-briefcase' },
    { label: 'education', cmd: 'education', icon: 'fa-solid fa-graduation-cap' },
    { label: 'contact', cmd: 'contact', icon: 'fa-solid fa-paper-plane' },
    { label: 'ping', cmd: 'ping', icon: 'fa-solid fa-network-wired' },
    { label: 'clear', cmd: 'clear', icon: 'fa-solid fa-trash-can' },
    { label: 'help', cmd: 'help', icon: 'fa-solid fa-circle-question' },
];

const scrollToBottom = async () => {
    await nextTick();
    if (terminalContainer.value) {
        terminalContainer.value.scrollTop = terminalContainer.value.scrollHeight;
    }
};

const executeCommand = (cmdStr: string) => {
    const rawCmd = cmdStr.trim();
    if (!rawCmd) return;

    enteredCommands.value.push(rawCmd);
    historyIndex.value = -1;

    const lower = rawCmd.toLowerCase();

    if (lower === 'clear' || lower === 'cls') {
        history.value = [];
        currentInput.value = '';
        scrollToBottom();
        return;
    }

    let outputType: TerminalEntry['outputType'] = 'unknown';

    if (lower === 'whoami' || lower.startsWith('whoami')) {
        outputType = 'whoami';
    } else if (lower === 'stack' || lower.includes('stack')) {
        outputType = 'stack';
    } else if (lower === 'projects' || lower === 'portfolio' || lower === 'work') {
        outputType = 'projects';
    } else if (lower === 'skills' || lower === 'skill') {
        outputType = 'skills';
    } else if (lower === 'experience' || lower === 'exp' || lower === 'career') {
        outputType = 'experience';
    } else if (lower === 'education' || lower === 'edu') {
        outputType = 'education';
    } else if (lower === 'contact' || lower === 'email' || lower === 'phone') {
        outputType = 'contact';
    } else if (lower === 'ping') {
        outputType = 'ping';
    } else if (lower === 'help' || lower === 'man' || lower === '?') {
        outputType = 'help';
    }

    history.value.push({
        id: Date.now(),
        command: rawCmd,
        outputType,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    });

    currentInput.value = '';
    scrollToBottom();
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
        executeCommand(currentInput.value);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (enteredCommands.value.length > 0) {
            if (historyIndex.value === -1) {
                historyIndex.value = enteredCommands.value.length - 1;
            } else if (historyIndex.value > 0) {
                historyIndex.value--;
            }
            currentInput.value = enteredCommands.value[historyIndex.value] || '';
        }
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (historyIndex.value !== -1) {
            if (historyIndex.value < enteredCommands.value.length - 1) {
                historyIndex.value++;
                currentInput.value = enteredCommands.value[historyIndex.value];
            } else {
                historyIndex.value = -1;
                currentInput.value = '';
            }
        }
    }
};

const focusTerminal = () => {
    commandInputRef.value?.focus();
};

onMounted(() => {
    scrollToBottom();
});
</script>

<template>
    <div
        class="w-full font-mono text-xs text-slate-300 flex flex-col select-text"
        @click="focusTerminal"
    >
        <!-- Terminal Body & Outputs (Scrollable) -->
        <div
            ref="terminalContainer"
            class="p-4 sm:p-6 space-y-6 overflow-y-auto no-scrollbar max-h-[460px] sm:max-h-[500px]"
        >
            <!-- 1. SYSTEM BANNER & HEADLINE CONTENT (Requested by user to be inside cli.console) -->
            <div class="space-y-4 pb-4 border-b border-white/[0.08]">
                <!-- Status Badge -->
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 shadow-[0_0_12px_rgba(16,185,129,0.15)]">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{{ profile?.availability_badge_text || 'Available for freelance work' }}</span>
                    </span>
                    <span class="text-[10px] text-slate-500 font-mono hidden sm:inline">// SYSTEM: READY</span>
                </div>

                <!-- Main Headline -->
                <div class="space-y-2">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight font-sans">
                        {{ profile?.hero_headline || 'Building software that matters.' }}
                    </h1>
                    <p class="text-slate-400 text-xs sm:text-sm font-sans leading-relaxed max-w-xl">
                        {{ profile?.hero_subheadline || 'Full Stack Engineer crafting robust, scalable, and beautiful digital experiences - from API design to pixel-perfect interfaces.' }}
                    </p>
                </div>

                <!-- Action Buttons Inside Terminal -->
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a
                        href="/download-cv"
                        download
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#d97736] hover:bg-[#e58546] text-[#0a0c10] font-mono font-bold text-xs shadow-[0_4px_16px_rgba(217,119,54,0.3)] hover:shadow-[0_6px_24px_rgba(217,119,54,0.45)] hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer group"
                    >
                        <span>Download CV</span>
                        <i class="fa-solid fa-download text-[11px] transition-transform group-hover:translate-y-0.5"></i>
                    </a>

                    <Link
                        href="/portfolio"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] text-white border border-white/[0.12] hover:border-[#d97736]/50 font-mono font-bold text-xs transition-all hover:-translate-y-0.5 active:translate-y-0 group"
                    >
                        <span>View work</span>
                        <i class="fa-solid fa-arrow-right text-[11px] text-[#d97736] transition-transform group-hover:translate-x-0.5"></i>
                    </Link>

                </div>
            </div>

            <!-- 2. TERMINAL COMMAND HISTORY -->
            <div v-for="entry in history" :key="entry.id" class="space-y-2">
                <!-- Command Prompt Line -->
                <div class="flex items-center gap-2 text-slate-400 text-xs">
                    <span class="text-[#d97736] font-semibold">fatz@dev</span>
                    <span class="text-slate-600">:</span>
                    <span class="text-sky-400 font-medium">~</span>
                    <span class="text-slate-500">$</span>
                    <span class="text-white font-bold">{{ entry.command }}</span>
                    <span class="text-[10px] text-slate-600 ml-auto font-mono">{{ entry.timestamp }}</span>
                </div>

                <!-- Command Output: WHOAMI -->
                <div v-if="entry.outputType === 'whoami'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                        <div><span class="text-slate-500">Name:</span> <span class="text-white font-bold">{{ profile?.full_name || 'FatzDev' }}</span></div>
                        <div><span class="text-slate-500">Role:</span> <span class="text-[#d97736] font-semibold">{{ profile?.professional_title || 'Full Stack Software Engineer' }}</span></div>
                        <div><span class="text-slate-500">Status:</span> <span class="text-emerald-400 font-semibold">{{ profile?.availability_badge_text || 'Available for freelance' }}</span></div>
                        <div><span class="text-slate-500">Location:</span> <span class="text-slate-300">{{ profile?.location || 'Jakarta, Indonesia · Remote' }}</span></div>
                        <div><span class="text-slate-500">Delivered:</span> <span class="text-white font-mono">{{ profile?.projects_delivered || '47+' }} projects</span></div>
                        <div><span class="text-slate-500">Experience:</span> <span class="text-white font-mono">{{ profile?.years_experience || '5+' }} years</span></div>
                    </div>
                    <p class="text-slate-400 text-[11px] pt-1.5 border-t border-white/[0.04] leading-relaxed">
                        {{ profile?.bio_summary_1 || 'Passionate engineer crafting production-grade web systems with modern frameworks.' }}
                    </p>
                </div>

                <!-- Command Output: STACK -->
                <div v-else-if="entry.outputType === 'stack'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2 text-xs">
                    <div class="text-[11px] text-[#d97736] font-bold uppercase tracking-wider">// System Architecture & Core Stack</div>
                    <div class="space-y-1.5 text-[11px]">
                        <div><span class="text-sky-400 font-bold">Frontend:</span> <span class="text-slate-300">Vue 3, React, TypeScript, Tailwind CSS, Inertia.js</span></div>
                        <div><span class="text-amber-400 font-bold">Backend:</span> <span class="text-slate-300">Laravel 12, PHP 8.3, Node.js, Python, REST APIs</span></div>
                        <div><span class="text-emerald-400 font-bold">Database:</span> <span class="text-slate-300">PostgreSQL, MySQL, Redis, SQLite</span></div>
                        <div><span class="text-purple-400 font-bold">DevOps:</span> <span class="text-slate-300">Docker, Git, CI/CD Pipelines, Linux, Nginx, AWS</span></div>
                    </div>
                </div>

                <!-- Command Output: PROJECTS -->
                <div v-else-if="entry.outputType === 'projects'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2.5 text-xs">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-[#d97736] font-bold uppercase tracking-wider">// Selected Portfolio Highlights</span>
                        <Link href="/portfolio" class="text-sky-400 hover:underline">View all &rarr;</Link>
                    </div>
                    <div class="space-y-2 text-[11px]">
                        <div class="p-2 rounded-lg bg-white/[0.02] border border-white/[0.04] flex items-center justify-between">
                            <div>
                                <span class="text-white font-bold">Logistics & Supply Chain Hub</span>
                                <p class="text-slate-500 text-[10px]">Vue 3, Laravel, Tailwind, Real-time WebSocket</p>
                            </div>
                            <Link href="/portfolio" class="text-xs text-[#d97736] hover:underline font-bold font-mono">[Open]</Link>
                        </div>
                        <div class="p-2 rounded-lg bg-white/[0.02] border border-white/[0.04] flex items-center justify-between">
                            <div>
                                <span class="text-white font-bold">HealthTech Telemedicine Portal</span>
                                <p class="text-slate-500 text-[10px]">React, Inertia.js, HIPAA compliant backend</p>
                            </div>
                            <Link href="/portfolio" class="text-xs text-[#d97736] hover:underline font-bold font-mono">[Open]</Link>
                        </div>
                    </div>
                </div>

                <!-- Command Output: SKILLS -->
                <div v-else-if="entry.outputType === 'skills'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2 text-xs">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-[#d97736] font-bold uppercase tracking-wider">// Engineering Capabilities</span>
                        <Link href="/skills" class="text-sky-400 hover:underline">Full matrix &rarr;</Link>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span v-for="sk in ['Vue 3', 'React', 'TypeScript', 'Laravel', 'PHP', 'Tailwind CSS', 'Node.js', 'PostgreSQL', 'Docker', 'REST APIs', 'Git', 'Three.js', 'GSAP']" :key="sk" class="px-2 py-0.5 rounded bg-white/[0.04] border border-white/[0.08] text-[10px] text-slate-300 font-mono">
                            {{ sk }}
                        </span>
                    </div>
                </div>

                <!-- Command Output: EXPERIENCE -->
                <div v-else-if="entry.outputType === 'experience'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2 text-xs">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-[#d97736] font-bold uppercase tracking-wider">// Career Timeline</span>
                        <Link href="/experience" class="text-sky-400 hover:underline">Full details &rarr;</Link>
                    </div>
                    <div class="space-y-1.5 text-[11px]">
                        <div><span class="text-emerald-400 font-bold">2021 - Present:</span> <span class="text-white font-semibold">Senior Full Stack Engineer</span> <span class="text-slate-500">@ Stellar Labs</span></div>
                        <div><span class="text-slate-400 font-bold">2018 - 2021:</span> <span class="text-white font-semibold">Full Stack Developer</span> <span class="text-slate-500">@ Quantum Digital</span></div>
                        <div><span class="text-slate-500 font-bold">2015 - 2018:</span> <span class="text-white font-semibold">Junior Developer</span> <span class="text-slate-500">@ DevHaus Agency</span></div>
                    </div>
                </div>

                <!-- Command Output: EDUCATION -->
                <div v-else-if="entry.outputType === 'education'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2 text-xs">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-[#d97736] font-bold uppercase tracking-wider">// Academic Background</span>
                        <Link href="/education" class="text-sky-400 hover:underline">View diplomas &rarr;</Link>
                    </div>
                    <div class="space-y-1.5 text-[11px]">
                        <div><span class="text-[#d97736] font-bold">Bachelor of Science in Computer Science</span> <span class="text-slate-500">&middot; 2015 - 2019</span></div>
                        <p class="text-slate-400 text-[10px]">Major in Software Engineering and Distributed Systems.</p>
                    </div>
                </div>

                <!-- Command Output: CONTACT -->
                <div v-else-if="entry.outputType === 'contact'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2 text-xs">
                    <div class="text-[11px] text-[#d97736] font-bold uppercase tracking-wider">// Direct Communication Channels</div>
                    <div class="space-y-1.5 text-[11px]">
                        <div><span class="text-slate-500">Email:</span> <a :href="'mailto:' + (profile?.email || 'fatzdev@example.com')" class="text-sky-400 hover:underline font-bold">{{ profile?.email || 'fatzdev@example.com' }}</a></div>
                        <div><span class="text-slate-500">Phone:</span> <a :href="'tel:' + (profile?.phone || '+19075550101')" class="text-white font-bold">{{ profile?.phone || '+1 (907) 555-0101' }}</a></div>
                        <div><span class="text-slate-500">Hours:</span> <span class="text-slate-300">Monday - Friday · 9AM - 6PM GMT</span></div>
                    </div>
                </div>

                <!-- Command Output: PING -->
                <div v-else-if="entry.outputType === 'ping'" class="p-3 rounded-xl bg-black/40 border border-white/[0.06] font-mono text-[11px] space-y-1 text-emerald-400">
                    <div>PING fatz.dev (127.0.0.1): 56 data bytes</div>
                    <div>64 bytes from 127.0.0.1: icmp_seq=0 ttl=64 time=0.412 ms</div>
                    <div>64 bytes from 127.0.0.1: icmp_seq=1 ttl=64 time=0.389 ms</div>
                    <div class="text-slate-400 pt-1 border-t border-white/[0.06]">--- fatz.dev ping statistics --- 2 packets transmitted, 0% packet loss</div>
                    <div class="text-[#d97736] font-bold">STATUS: 100% OPERATIONAL · ALL SERVICES ONLINE</div>
                </div>

                <!-- Command Output: HELP -->
                <div v-else-if="entry.outputType === 'help'" class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-2 text-xs">
                    <div class="text-[11px] text-[#d97736] font-bold uppercase tracking-wider">// Available Interactive Commands</div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px]">
                        <div><span class="text-sky-400 font-bold">whoami</span> <span class="text-slate-500">- Profile summary</span></div>
                        <div><span class="text-sky-400 font-bold">stack</span> <span class="text-slate-500">- Tech stack info</span></div>
                        <div><span class="text-sky-400 font-bold">projects</span> <span class="text-slate-500">- Showcase list</span></div>
                        <div><span class="text-sky-400 font-bold">skills</span> <span class="text-slate-500">- Core abilities</span></div>
                        <div><span class="text-sky-400 font-bold">experience</span> <span class="text-slate-500">- Career timeline</span></div>
                        <div><span class="text-sky-400 font-bold">education</span> <span class="text-slate-500">- Academic degrees</span></div>
                        <div><span class="text-sky-400 font-bold">contact</span> <span class="text-slate-500">- Direct phone & mail</span></div>
                        <div><span class="text-sky-400 font-bold">ping</span> <span class="text-slate-500">- Check latency</span></div>
                        <div><span class="text-sky-400 font-bold">clear</span> <span class="text-slate-500">- Clear screen</span></div>
                    </div>
                </div>

                <!-- Command Output: UNKNOWN -->
                <div v-else class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
                    <div>zsh: command not found: {{ entry.command }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Type <span class="text-[#d97736] font-bold">help</span> to view all available commands or click the presets below.</div>
                </div>
            </div>

            <!-- 3. ACTIVE COMMAND LINE INPUT -->
            <div class="flex items-center gap-2 text-xs pt-1">
                <span class="text-[#d97736] font-semibold shrink-0">fatz@dev</span>
                <span class="text-slate-600 shrink-0">:</span>
                <span class="text-sky-400 font-medium shrink-0">~</span>
                <span class="text-slate-500 shrink-0">$</span>
                <div class="relative flex-1 flex items-center">
                    <input
                        ref="commandInputRef"
                        v-model="currentInput"
                        type="text"
                        placeholder="Type a command (whoami, stack, projects, ping, help)..."
                        class="w-full bg-transparent border-0 outline-none p-0 text-white font-mono text-xs focus:ring-0 placeholder:text-slate-600 focus:outline-none"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                        @keydown="handleKeydown"
                    />
                </div>
            </div>
        </div>

        <!-- 4. BOTTOM PRESET RUN BAR (Expanded with multiple commands as requested) -->
        <div class="px-4 py-3 bg-black/40 border-t border-white/[0.08] flex items-center gap-2 overflow-x-auto no-scrollbar select-none">
            <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold shrink-0">RUN:</span>
            <div class="flex items-center gap-1.5">
                <button
                    v-for="btn in presetCommands"
                    :key="btn.cmd"
                    type="button"
                    @click.stop="executeCommand(btn.cmd)"
                    class="px-2.5 py-1 rounded-lg bg-white/[0.04] hover:bg-[#d97736]/15 hover:text-[#d97736] hover:border-[#d97736]/30 border border-white/[0.08] text-[11px] font-mono font-medium text-slate-400 transition-all active:scale-95 shrink-0 flex items-center gap-1.5 cursor-pointer group"
                >
                    <i :class="[btn.icon, 'text-[9px] text-slate-500 group-hover:text-[#d97736]']"></i>
                    <span>{{ btn.label }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
