<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Calculator, LoaderCircle, LogIn, Zap } from '@lucide/vue';
import { computed } from 'vue';
import InsufficientCreditsNotice from '@/components/generate/InsufficientCreditsNotice.vue';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { formatMoney, formatMoneyRange } from '@/lib/money';
import { login } from '@/routes';
import type { EstimateLine, GenerationEstimate } from '@/types';

withDefaults(
    defineProps<{
        estimate: GenerationEstimate | null;
        isEstimating: boolean;
        isEstimateUnavailable: boolean;
        isSubmitting: boolean;
        isPromptEmpty: boolean;
        hasInsufficientBalance?: boolean;
    }>(),
    {
        hasInsufficientBalance: false,
    },
);

const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);

const lines: {
    key: keyof GenerationEstimate['lines'];
    label: string;
    isEstimate: boolean;
}[] = [
    { key: 'text', label: 'Prompt text', isEstimate: true },
    { key: 'reference_images', label: 'Reference images', isEstimate: false },
    { key: 'output', label: 'Output image', isEstimate: false },
    { key: 'reasoning', label: 'Reasoning', isEstimate: true },
];

function formatTokens(line: EstimateLine): string {
    return line.tokens_min === line.tokens_max
        ? `${line.tokens_min.toLocaleString()} tokens`
        : `${line.tokens_min.toLocaleString()}–${line.tokens_max.toLocaleString()} tokens`;
}
</script>

<template>
    <TooltipProvider>
        <section
            class="flex flex-col gap-3 rounded-lg border border-border-subtle bg-surface-raised p-4"
        >
            <div
                class="flex items-center justify-between border-b border-border-subtle pb-2"
            >
                <div class="flex items-center gap-1">
                    <Calculator class="size-4.5 text-primary" />
                    <h2 class="font-display text-headline-sm text-on-surface">
                        Cost estimate
                    </h2>
                </div>
                <LoaderCircle
                    v-if="isEstimating"
                    class="size-4 animate-spin text-outline"
                />
            </div>

            <p
                v-if="isEstimateUnavailable && !estimate"
                class="py-4 text-center text-body-dense text-on-surface-variant"
            >
                The estimate is unavailable right now. Try again in a moment.
            </p>

            <div v-else-if="!estimate" class="flex flex-col gap-2 py-1">
                <Skeleton class="h-8 w-3/4" />
                <Skeleton class="h-24 w-full" />
            </div>

            <div
                v-else
                class="flex flex-col gap-2 transition-opacity"
                :class="{ 'opacity-60': isEstimating }"
            >
                <div class="flex flex-wrap items-baseline gap-1 py-0.5">
                    <span
                        class="font-mono text-[26px] leading-tight font-bold tracking-tight text-on-surface tabular-nums"
                    >
                        {{ formatMoney(estimate.total_min_nano) }}
                    </span>
                    <span
                        class="font-mono text-headline-sm font-normal text-outline"
                    >
                        –
                    </span>
                    <span
                        class="font-mono text-[26px] leading-tight font-bold tracking-tight text-primary tabular-nums"
                    >
                        {{ formatMoney(estimate.total_max_nano, 4, 'up') }}
                    </span>
                    <span class="ml-1 font-mono text-label-dense text-outline">
                        USD
                    </span>
                </div>

                <div
                    class="flex flex-col divide-y divide-border-subtle border-y border-border-subtle py-0.5 text-label-default"
                >
                    <div
                        v-for="line in lines"
                        :key="line.key"
                        class="flex items-start justify-between gap-2 py-1"
                    >
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-on-surface">
                                    {{ line.label }}
                                </span>
                                <Tooltip v-if="line.isEstimate">
                                    <TooltipTrigger as-child>
                                        <span
                                            class="cursor-help rounded-full border border-border-prominent bg-surface-container-lowest px-1 font-mono text-[9px] text-outline uppercase"
                                        >
                                            est
                                        </span>
                                    </TooltipTrigger>
                                    <TooltipContent class="w-48">
                                        The exact token count is only known once
                                        the image completes.
                                    </TooltipContent>
                                </Tooltip>
                            </div>
                            <span class="font-mono text-[11px] text-outline">
                                {{ formatTokens(estimate.lines[line.key]) }}
                            </span>
                        </div>
                        <span
                            class="shrink-0 font-mono text-on-surface tabular-nums"
                        >
                            {{
                                formatMoneyRange(
                                    estimate.lines[line.key].price_min_nano,
                                    estimate.lines[line.key].price_max_nano,
                                )
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <p class="text-label-dense text-on-surface-variant">
                You're charged the actual cost when the image completes. Failed
                generations are free.
            </p>

            <template v-if="isAuthenticated">
                <button
                    type="submit"
                    :disabled="
                        isSubmitting || isPromptEmpty || hasInsufficientBalance
                    "
                    class="hidden w-full items-center justify-center gap-1 rounded bg-primary py-3 font-display text-headline-sm font-bold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)] transition-all hover:bg-primary-container hover:shadow-[0_0_18px_rgba(154,230,0,0.4)] active:scale-[0.99] disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none lg:flex"
                >
                    <LoaderCircle
                        v-if="isSubmitting"
                        class="size-4.5 animate-spin"
                    />
                    <Zap v-else class="size-4.5" />
                    Generate image
                </button>

                <InsufficientCreditsNotice
                    v-if="hasInsufficientBalance && estimate"
                    :max-cost="formatMoney(estimate.total_max_nano, 4, 'up')"
                    :balance="formatMoney(page.props.balanceNano ?? 0)"
                />
            </template>

            <Link
                v-else
                :href="login()"
                class="hidden w-full items-center justify-center gap-1 rounded bg-primary py-3 font-display text-headline-sm font-bold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)] transition-all hover:bg-primary-container lg:flex"
            >
                <LogIn class="size-4.5" />
                Log in to generate
            </Link>
        </section>
    </TooltipProvider>
</template>
