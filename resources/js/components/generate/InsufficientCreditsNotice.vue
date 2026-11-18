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
        class="flex flex-col gap-1 rounded border border-rose-500/20 bg-rose-500/10 p-2"
    >
        <p class="text-[12px] leading-snug font-medium text-error">
            Not enough credits — max cost {{ maxCost }}, balance {{ balance }}
        </p>
        <button
            type="button"
            class="flex items-center gap-1 self-start text-label-dense font-medium text-primary hover:underline"
            @click="openBuyCredits"
        >
            Buy credits
            <ArrowRight class="size-3" />
        </button>
    </div>
</template>
