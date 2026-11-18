<script setup lang="ts">
import { ArrowRight, TriangleAlert, X } from '@lucide/vue';
import { useBuyCreditsDialog } from '@/composables/useBuyCreditsDialog';

defineProps<{
    balance: string;
}>();

defineEmits<{
    dismiss: [];
}>();

const { openBuyCredits } = useBuyCreditsDialog();
</script>

<template>
    <div
        class="flex items-center justify-between gap-2 rounded-lg border border-outline-variant/30 bg-surface-container-high px-2 py-0.5 text-on-surface shadow-sm"
    >
        <div class="flex min-w-0 items-center gap-2">
            <div
                class="flex size-6 shrink-0 items-center justify-center rounded bg-amber-500/10 text-amber-400"
            >
                <TriangleAlert class="size-4" />
            </div>
            <p class="truncate text-body-dense">
                <span class="font-medium text-on-surface">
                    Low balance warning:
                </span>
                <span class="text-on-surface-variant">
                    Your balance (<span
                        class="font-mono text-primary tabular-nums"
                        >{{ balance }}</span
                    >) is running low. Consider topping up to prevent generation
                    interruption.
                </span>
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <button
                type="button"
                class="flex items-center gap-0.5 rounded px-2 py-0.5 text-label-default text-primary transition-colors hover:bg-surface-container hover:text-on-surface"
                @click="openBuyCredits"
            >
                Buy Credits
                <ArrowRight class="size-3.5" />
            </button>
            <button
                type="button"
                title="Dismiss"
                class="rounded p-0.5 text-on-surface-variant transition-colors hover:text-on-surface"
                @click="$emit('dismiss')"
            >
                <X class="size-4" />
            </button>
        </div>
    </div>
</template>
