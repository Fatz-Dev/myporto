<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { onBeforeUnmount, watch } from 'vue';
import {
    Bold,
    Italic,
    Strikethrough,
    Code,
    Heading1,
    Heading2,
    Heading3,
    Pilcrow,
    List,
    ListOrdered,
    Quote,
    FileCode,
    Minus,
    Undo,
    Redo
} from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
    }>(),
    {
        modelValue: '',
        placeholder: 'Tulis isi konten artikel di sini...',
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'tiptap-content min-h-[220px] max-h-[460px] overflow-y-auto p-4 text-sm text-slate-200 focus:outline-none leading-relaxed',
        },
    },
    onUpdate: () => {
        emit('update:modelValue', editor.value?.getHTML() || '');
    },
});

watch(
    () => props.modelValue,
    (val) => {
        if (editor.value && editor.value.getHTML() !== val) {
            editor.value.commands.setContent(val || '', false);
        }
    }
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<template>
    <div class="rounded-2xl bg-white/[0.02] border border-white/[0.08] overflow-hidden focus-within:border-[#d97736] transition-colors">
        <!-- Toolbar Header -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-2 bg-white/[0.03] border-b border-white/[0.06] text-slate-300">
            <!-- Format Teks -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="editor.isActive('bold') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Tebal (Bold)"
            >
                <Bold class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="editor.isActive('italic') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Miring (Italic)"
            >
                <Italic class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleStrike().run()"
                :class="editor.isActive('strike') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Coret (Strikethrough)"
            >
                <Strikethrough class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleCode().run()"
                :class="editor.isActive('code') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Kode Inline"
            >
                <Code class="w-4 h-4" />
            </button>

            <div class="h-4 w-px bg-white/[0.1] mx-1"></div>

            <!-- Headings -->
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="editor.isActive('heading', { level: 1 }) ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Heading 1"
            >
                <Heading1 class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="editor.isActive('heading', { level: 2 }) ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Heading 2"
            >
                <Heading2 class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="editor.isActive('heading', { level: 3 }) ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Heading 3"
            >
                <Heading3 class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().setParagraph().run()"
                :class="editor.isActive('paragraph') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Paragraf Normal"
            >
                <Pilcrow class="w-4 h-4" />
            </button>

            <div class="h-4 w-px bg-white/[0.1] mx-1"></div>

            <!-- Lists & Quotes -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="editor.isActive('bulletList') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Daftar Poin (Bullet List)"
            >
                <List class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="editor.isActive('orderedList') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Daftar Angka (Ordered List)"
            >
                <ListOrdered class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="editor.isActive('blockquote') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Kutipan (Blockquote)"
            >
                <Quote class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleCodeBlock().run()"
                :class="editor.isActive('codeBlock') ? 'bg-[#d97736] text-white' : 'hover:bg-white/[0.06] text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg transition-colors"
                title="Blok Kode (Code Block)"
            >
                <FileCode class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().setHorizontalRule().run()"
                class="p-1.5 rounded-lg hover:bg-white/[0.06] text-slate-400 hover:text-white transition-colors"
                title="Garis Pemisah"
            >
                <Minus class="w-4 h-4" />
            </button>

            <div class="h-4 w-px bg-white/[0.1] mx-1"></div>

            <!-- Undo / Redo -->
            <button
                type="button"
                @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().undo()"
                class="p-1.5 rounded-lg hover:bg-white/[0.06] text-slate-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                title="Undo (Urungkan)"
            >
                <Undo class="w-4 h-4" />
            </button>

            <button
                type="button"
                @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().redo()"
                class="p-1.5 rounded-lg hover:bg-white/[0.06] text-slate-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                title="Redo (Ulangi)"
            >
                <Redo class="w-4 h-4" />
            </button>
        </div>

        <!-- Editor Content Area -->
        <EditorContent :editor="editor" />
    </div>
</template>

<style>
/* Tiptap content styling */
.tiptap-content h1 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #ffffff;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    line-height: 1.25;
}

.tiptap-content h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
    margin-top: 0.85rem;
    margin-bottom: 0.4rem;
    line-height: 1.3;
}

.tiptap-content h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #f1f5f9;
    margin-top: 0.75rem;
    margin-bottom: 0.35rem;
}

.tiptap-content p {
    margin-bottom: 0.65rem;
}

.tiptap-content ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}

.tiptap-content ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}

.tiptap-content li {
    margin-bottom: 0.25rem;
}

.tiptap-content blockquote {
    border-left: 3px solid #d97736;
    padding-left: 1rem;
    font-style: italic;
    color: #94a3b8;
    margin: 0.75rem 0;
}

.tiptap-content pre {
    background-color: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    font-family: monospace;
    font-size: 0.85rem;
    color: #38bdf8;
    margin: 0.75rem 0;
    overflow-x: auto;
}

.tiptap-content code {
    background-color: rgba(255, 255, 255, 0.08);
    padding: 0.15rem 0.35rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.85em;
    color: #f43f5e;
}

.tiptap-content hr {
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin: 1rem 0;
}
</style>