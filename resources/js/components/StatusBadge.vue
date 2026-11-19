<script setup lang="ts">
import { CircleCheck, CircleX } from '@lucide/vue';
import type { GenerationStatus } from '@/types';

defineProps<{
    status: GenerationStatus;
    label: string;
}>();

const toneClasses: Record<GenerationStatus, string> = {
    pending: 'bg-surface-container-high text-on-surface-variant',
    queued: 'bg-sky-500/10 text-sky-400',
    processing: 'bg-amber-500/10 text-amber-400',
    succeeded: 'bg-green-500/10 text-green-400',
    failed: 'bg-rose-500/10 text-rose-400',
    submission_failed: 'bg-rose-500/10 text-rose-400',
};

const dotClasses: Partial<Record<GenerationStatus, string>> = {
    pending: 'bg-outline',
    queued: 'bg-sky-400',
    processing: 'bg-amber-400 animate-pulse',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full px-1 py-0.5 font-mono text-mono-badge uppercase"
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
