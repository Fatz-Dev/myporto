<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    modelValue?: string;
}>();

const emits = defineEmits<{
    'update:modelValue': [value: string];
}>();

const delegatedProps = computed(() => {
    const { class: _, modelValue: __, ...rest } = props as any;
    return rest;
});
</script>

<template>
    <textarea
        :class="cn(
            'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
            props.class,
        )"
        :value="modelValue"
        @input="emits('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        v-bind="delegatedProps"
    />
</template>
