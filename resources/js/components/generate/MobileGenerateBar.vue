<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LoaderCircle, LogIn, Plus, Zap } from '@lucide/vue';
import { computed } from 'vue';
import { useBuyCreditsDialog } from '@/composables/useBuyCreditsDialog';
import { formatMoneyRange } from '@/lib/money';
import { login } from '@/routes';
import type { GenerationEstimate } from '@/types';

/**
 * Small screens stack the form, which pushes the cost panel and its button far down.
 * This bar keeps the estimate and the Generate button in reach at the bottom of the screen.
 * It sits inside the form, so its button submits it.
 */
const props = defineProps<{
    estimate: GenerationEstimate | null;
    isEstimating: boolean;
    isEstimateUnavailable: boolean;
    isSubmitting: boolean;
    isPromptEmpty: boolean;
    hasInsufficientBalance: boolean;
}>();

const { openBuyCredits } = useBuyCreditsDialog();

const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);

const estimateLabel = computed(() => {
    if (props.estimate) {
        return formatMoneyRange(
            props.estimate.total_min_nano,
            props.estimate.total_max_nano,
        );
    }

    return props.isEstimateUnavailable ? 'Unavailable' : '…';
});
</script>

<template>
    <div
        class="fixed inset-x-0 bottom-0 z-30 border-t border-border-subtle bg-surface-container-lowest/95 px-4 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] backdrop-blur-md md:left-64 lg:hidden"
    >
        <div class="mx-auto flex max-w-3xl items-center gap-3">
            <div class="flex min-w-0 flex-col">
                <span
                    class="font-mono text-mono-badge tracking-[0.04em] text-outline uppercase"
                >
                    Estimated cost
                </span>
                <span
                    class="truncate font-mono text-mono-metric font-semibold text-primary tabular-nums transition-opacity"
                    :class="{ 'opacity-60': isEstimating }"
                >
                    {{ estimateLabel }}
                </span>
            </div>

            <button
                v-if="isAuthenticated && hasInsufficientBalance"
                type="button"
                class="ml-auto flex shrink-0 items-center gap-1 rounded bg-primary px-4 py-2.5 font-display text-headline-sm font-bold text-primary-foreground transition-colors hover:bg-primary-container"
                @click="openBuyCredits"
            >
                <Plus class="size-4.5" />
                Buy credits
            </button>

            <button
                v-else-if="isAuthenticated"
                type="submit"
                :disabled="isSubmitting || isPromptEmpty"
                class="ml-auto flex shrink-0 items-center gap-1 rounded bg-primary px-4 py-2.5 font-display text-headline-sm font-bold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)] transition-colors hover:bg-primary-container active:scale-[0.99] disabled:pointer-events-none disabled:opacity-50"
            >
                <LoaderCircle
                    v-if="isSubmitting"
                    class="size-4.5 animate-spin"
                />
                <Zap v-else class="size-4.5" />
                Generate
            </button>

            <Link
                v-else
                :href="login()"
                class="ml-auto flex shrink-0 items-center gap-1 rounded bg-primary px-4 py-2.5 font-display text-headline-sm font-bold text-primary-foreground"
            >
                <LogIn class="size-4.5" />
                Log in
            </Link>
        </div>
    </div>
</template>
