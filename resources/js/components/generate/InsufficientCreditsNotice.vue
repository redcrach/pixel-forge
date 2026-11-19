<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import { useBuyCreditsDialog } from '@/composables/useBuyCreditsDialog';

defineProps<{
    maxCost: string;
    balance: string;
}>();

const { openBuyCredits } = useBuyCreditsDialog();
</script>

<template>
    <div
        class="flex items-start gap-2 bg-surface-container-high p-2 shadow-pixel-inset"
    >
        <span
            class="flex size-6 shrink-0 items-center justify-center bg-error-container font-display font-bold text-error"
            aria-hidden="true"
        >
            !
        </span>
        <div class="flex flex-col gap-1">
            <p class="text-[12px] leading-snug font-bold text-error uppercase">
                Not enough credits — max cost {{ maxCost }}, balance
                {{ balance }}
            </p>
            <button
                type="button"
                class="flex items-center gap-1 self-start text-label-dense font-bold text-wheat uppercase hover:underline"
                @click="openBuyCredits"
            >
                [ Insert coin ]
                <ArrowRight class="size-3" />
            </button>
        </div>
    </div>
</template>
