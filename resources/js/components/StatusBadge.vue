<script setup lang="ts">
import { CircleCheck, CircleX } from '@lucide/vue';
import type { GenerationStatus } from '@/types';

defineProps<{
    status: GenerationStatus;
    label: string;
}>();

const toneClasses: Record<GenerationStatus, string> = {
    pending:
        'border-border-prominent bg-surface-container-high text-on-surface-variant',
    queued: 'border-tertiary bg-tertiary text-on-tertiary-container',
    processing:
        'border-amber-400/60 bg-surface-container-lowest text-amber-400',
    succeeded: 'border-primary bg-primary text-primary-foreground',
    failed: 'border-error-container bg-error-container text-error',
    submission_failed: 'border-error-container bg-error-container text-error',
};

const dotClasses: Partial<Record<GenerationStatus, string>> = {
    pending: 'bg-outline',
    queued: 'bg-on-tertiary-container',
    processing: 'bg-amber-400 animate-pulse',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-1 border px-1.5 py-0.5 font-mono text-mono-badge font-semibold tracking-[0.04em] uppercase"
        :class="toneClasses[status]"
    >
        <CircleCheck v-if="status === 'succeeded'" class="size-3" />
        <CircleX
            v-else-if="status === 'failed' || status === 'submission_failed'"
            class="size-3"
        />
        <span v-else class="size-1.5" :class="dotClasses[status]" />
        {{ label }}
    </span>
</template>
