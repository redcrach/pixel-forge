<script setup lang="ts">
import { Info, X } from '@lucide/vue';

defineProps<{
    title: string;
    badge: string;
    tone: 'warning' | 'info';
}>();

defineEmits<{
    dismiss: [];
}>();
</script>

<template>
    <div
        class="pointer-events-auto flex items-start gap-2 rounded-lg border bg-surface-container-high/95 p-2 text-on-surface shadow-2xl backdrop-blur-md"
        :class="
            tone === 'warning'
                ? 'border-amber-500/30'
                : 'border-outline-variant/50'
        "
    >
        <div
            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded"
            :class="
                tone === 'warning'
                    ? 'bg-amber-500/10 text-amber-400'
                    : 'bg-surface-container-highest text-tertiary'
            "
        >
            <span v-if="tone === 'warning'" class="relative flex size-2">
                <span
                    class="absolute inline-flex size-full animate-ping rounded-full bg-amber-400 opacity-75"
                />
                <span
                    class="relative inline-flex size-2 rounded-full bg-amber-400"
                />
            </span>
            <Info v-else class="size-4" />
        </div>

        <div class="flex min-w-0 flex-1 flex-col">
            <div class="flex items-center justify-between gap-2">
                <span class="text-label-default font-semibold text-on-surface">
                    {{ title }}
                </span>
                <span
                    class="rounded px-1 font-mono text-mono-badge tabular-nums"
                    :class="
                        tone === 'warning'
                            ? 'bg-amber-500/20 text-amber-300'
                            : 'border border-tertiary/20 bg-tertiary/10 text-tertiary'
                    "
                >
                    {{ badge }}
                </span>
            </div>
            <div class="mt-0.5 text-body-dense text-on-surface-variant">
                <slot />
            </div>
        </div>

        <button
            type="button"
            title="Close"
            class="shrink-0 p-0.5 text-outline transition-colors hover:text-on-surface"
            @click="$emit('dismiss')"
        >
            <X class="size-3.5" />
        </button>
    </div>
</template>
