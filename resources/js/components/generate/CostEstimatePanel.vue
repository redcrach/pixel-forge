<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LoaderCircle, LogIn, Zap } from '@lucide/vue';
import { computed } from 'vue';
import InsufficientCreditsNotice from '@/components/generate/InsufficientCreditsNotice.vue';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { formatMoneyCompact, formatMoneyRange } from '@/lib/money';
import { login } from '@/routes';
import type { EstimateLine, GenerationEstimate } from '@/types';

const props = withDefaults(
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

const balanceNano = computed(() => page.props.balanceNano ?? 0);

/** What would be left if this run cost its maximum, the amount held when it is submitted. */
const balanceAfterMaxChargeNano = computed(() =>
    props.estimate === null
        ? null
        : balanceNano.value - props.estimate.total_max_nano,
);

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
            class="flex flex-col gap-3 border-2 border-border-subtle bg-surface-raised p-2 shadow-pixel"
        >
            <div
                class="flex items-center justify-between bg-surface-container-high px-2 py-1.5"
            >
                <div class="flex items-center gap-1.5">
                    <span class="size-3 animate-pulse bg-primary" />
                    <h2
                        class="font-display text-headline-sm font-bold tracking-wider text-primary uppercase"
                    >
                        [ Cost estimate ]
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
                <div
                    class="flex flex-col gap-0.5 bg-surface-container-lowest p-2 shadow-pixel-inset"
                >
                    <span
                        class="font-mono text-label-dense text-on-surface-variant uppercase"
                    >
                        Estimated run cost (USD)
                    </span>
                    <span
                        class="font-display text-[26px] leading-tight font-bold tracking-tight text-primary tabular-nums"
                    >
                        [ {{ formatMoneyCompact(estimate.total_min_nano) }} –
                        {{ formatMoneyCompact(estimate.total_max_nano) }}
                        ]
                    </span>
                </div>

                <div
                    class="flex flex-col divide-y-2 divide-surface-container-high bg-surface-container-low px-2 text-label-default"
                >
                    <div
                        v-for="line in lines"
                        :key="line.key"
                        class="flex items-start justify-between gap-2 py-1"
                    >
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="text-on-surface-variant uppercase">
                                    {{ line.label }}
                                </span>
                                <Tooltip v-if="line.isEstimate">
                                    <TooltipTrigger as-child>
                                        <span
                                            class="cursor-help border border-border-prominent bg-surface-container-lowest px-1 font-mono text-[9px] text-wheat uppercase"
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
                        <!-- A 9-decimal range is wide: it wraps at the dash rather than leave the panel -->
                        <span
                            class="min-w-0 text-right font-mono font-bold text-on-surface tabular-nums"
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
                <div
                    class="flex flex-col bg-surface-container-lowest py-1 text-label-default"
                >
                    <div
                        class="flex items-center justify-between gap-2 px-3 py-1"
                    >
                        <span class="text-on-surface-variant uppercase">
                            Current balance
                        </span>
                        <span
                            class="font-mono font-bold tracking-wider text-wheat tabular-nums"
                        >
                            {{ formatMoneyCompact(balanceNano) }}
                        </span>
                    </div>
                    <div
                        class="flex items-center justify-between gap-2 px-3 py-1"
                    >
                        <span class="text-on-surface-variant uppercase">
                            Balance after max charge
                            <span v-if="estimate" class="font-mono">
                                ({{
                                    formatMoneyCompact(estimate.total_max_nano)
                                }})
                            </span>
                        </span>
                        <span
                            class="flex shrink-0 items-center gap-1.5 font-mono font-bold tracking-wider tabular-nums"
                            :class="
                                hasInsufficientBalance
                                    ? 'text-error'
                                    : 'text-primary'
                            "
                        >
                            <span
                                class="size-1.5"
                                :class="
                                    hasInsufficientBalance
                                        ? 'bg-error'
                                        : 'bg-primary'
                                "
                            />
                            {{
                                balanceAfterMaxChargeNano === null
                                    ? '—'
                                    : formatMoneyCompact(
                                          balanceAfterMaxChargeNano,
                                      )
                            }}
                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="
                        isSubmitting || isPromptEmpty || hasInsufficientBalance
                    "
                    class="pixel-press hidden w-full items-center justify-center gap-1 bg-primary py-3 font-display text-headline-sm font-bold tracking-wider text-primary-foreground uppercase transition-colors hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none lg:flex"
                >
                    <LoaderCircle
                        v-if="isSubmitting"
                        class="size-4.5 animate-spin"
                    />
                    <Zap v-else class="size-4.5" />
                    [ Generate image ]
                </button>

                <InsufficientCreditsNotice
                    v-if="hasInsufficientBalance && estimate"
                    :max-cost="formatMoneyCompact(estimate.total_max_nano)"
                    :balance="formatMoneyCompact(page.props.balanceNano ?? 0)"
                />
            </template>

            <Link
                v-else
                :href="login()"
                class="pixel-press hidden w-full items-center justify-center gap-1 bg-primary py-3 font-display text-headline-sm font-bold tracking-wider text-primary-foreground uppercase transition-colors hover:bg-primary-container lg:flex"
            >
                <LogIn class="size-4.5" />
                [ Log in to generate ]
            </Link>
        </section>
    </TooltipProvider>
</template>
