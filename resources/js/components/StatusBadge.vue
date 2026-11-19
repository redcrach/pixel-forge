<script setup lang="ts">
import { CircleCheck, CircleX } from '@lucide/vue';
import type { GenerationStatus } from '@/types';

defineProps<{
    status: GenerationStatus;
    label: string;
}>();

const toneClasses: Record<GenerationStatus, string> = {
    pending: 'border-border-prominent bg-surface-container-high text-on-surface-variant',
    queued: 'border-tertiary/40 bg-tertiary/10 text-tertiary',
    processing: 'border-amber-400/40 bg-amber-400/10 text-amber-400',
    succeeded: 'border-primary/40 bg-primary/10 text-primary',
    failed: 'border-error/40 bg-error/10 text-error',
    submission_failed: 'border-error/40 bg-error/10 text-error',
};

const dotClasses: Partial<Record<GenerationStatus, string>> = {
    pending: 'bg-outline',
    queued: 'bg-tertiary',
    processing: 'bg-amber-400 animate-pulse',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full border px-1.5 py-0.5 font-mono text-mono-badge font-semibold tracking-[0.04em] uppercase"
        :class="toneClasses[status]"
    >
        <CircleCheck v-if="status === 'succeeded'" class="size-3" />
        <CircleX
            v-else-if="status === 'failed' || status === 'submission_failed'"
            class="size-3"
        />
        <span
            v-else
            class="size-1.5 rounded-full"
            :class="dotClasses[status]"
        />
        {{ label }}
    </span>
</template>
