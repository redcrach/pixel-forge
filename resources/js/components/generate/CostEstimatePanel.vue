<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Calculator, LogIn, Zap } from '@lucide/vue';
import { computed } from 'vue';
import InsufficientCreditsNotice from '@/components/generate/InsufficientCreditsNotice.vue';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { login } from '@/routes';
import type { CostLineItem } from '@/types';

withDefaults(
    defineProps<{
        lineItems: CostLineItem[];
        hasInsufficientBalance?: boolean;
    }>(),
    {
        hasInsufficientBalance: false,
    },
);

const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);
</script>

<template>
    <TooltipProvider>
        <section
            class="flex flex-col gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-lowest/95 p-4 shadow-md backdrop-blur"
        >
            <div
                class="flex items-center gap-1 border-b border-outline-variant/30 pb-1"
            >
                <Calculator class="size-4.5 text-primary" />
                <h2 class="text-headline-sm tracking-tight text-on-surface">
                    Cost estimate
                </h2>
            </div>

            <div class="flex flex-wrap items-baseline gap-1 py-0.5">
                <span
                    class="font-mono text-[26px] leading-tight font-bold tracking-tight text-on-surface tabular-nums"
                >
                    $0.0775
                </span>
                <span
                    class="font-mono text-headline-sm font-normal text-outline"
                >
                    –
                </span>
                <span
                    class="font-mono text-[26px] leading-tight font-bold tracking-tight text-primary tabular-nums"
                >
                    $0.1058
                </span>
                <span class="ml-1 font-mono text-label-dense text-outline">
                    USD
                </span>
            </div>

            <div
                class="flex flex-col divide-y divide-outline-variant/20 border-y border-outline-variant/30 py-0.5 text-label-default"
            >
                <div
                    v-for="item in lineItems"
                    :key="item.label"
                    class="flex items-start justify-between gap-2 py-1"
                >
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium text-on-surface">
                                {{ item.label }}
                            </span>
                            <Tooltip v-if="item.isEstimate">
                                <TooltipTrigger as-child>
                                    <span
                                        class="cursor-help rounded border border-outline-variant/30 bg-surface-container px-1 font-mono text-[9px] text-outline uppercase"
                                    >
                                        est
                                    </span>
                                </TooltipTrigger>
                                <TooltipContent class="w-48">
                                    The exact token count is only known once the
                                    image completes.
                                </TooltipContent>
                            </Tooltip>
                        </div>
                        <span class="font-mono text-[11px] text-outline">
                            {{ item.detail }}
                        </span>
                    </div>
                    <span
                        class="shrink-0 font-mono text-on-surface tabular-nums"
                    >
                        {{ item.amount }}
                    </span>
                </div>
            </div>

            <p class="text-label-dense text-on-surface-variant">
                You're charged the actual cost when the image completes. Failed
                generations are free.
            </p>

            <template v-if="isAuthenticated">
                <div
                    class="flex flex-col gap-0.5 rounded border border-outline-variant/30 bg-surface-container-low p-1 text-label-default"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-on-surface-variant">
                            Current balance
                        </span>
                        <span class="font-mono text-on-surface tabular-nums">
                            $4.2731
                        </span>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-outline-variant/20 pt-1"
                    >
                        <span class="text-on-surface-variant">
                            After max charge
                        </span>
                        <span
                            class="font-mono font-semibold text-green-400 tabular-nums"
                        >
                            $4.1673
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    :disabled="hasInsufficientBalance"
                    class="flex w-full items-center justify-center gap-1 rounded bg-primary py-2 text-headline-sm text-primary-foreground shadow-md transition-all hover:bg-primary-container active:scale-[0.99] disabled:pointer-events-none disabled:opacity-50"
                >
                    <Zap class="size-4.5" />
                    Generate image
                </button>

                <InsufficientCreditsNotice
                    v-if="hasInsufficientBalance"
                    max-cost="$0.1058"
                    balance="$0.0500"
                />
            </template>

            <template v-else>
                <Link
                    :href="login()"
                    class="flex w-full items-center justify-center gap-1 rounded bg-primary py-2 text-headline-sm text-primary-foreground shadow-md transition-all hover:bg-primary-container"
                >
                    <LogIn class="size-4.5" />
                    Log in to generate
                </Link>
                <p class="text-center text-label-dense text-on-surface-variant">
                    New accounts get $2.00 in free credits.
                </p>
            </template>
        </section>
    </TooltipProvider>
</template>
