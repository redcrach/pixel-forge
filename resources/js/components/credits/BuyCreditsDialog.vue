<script setup lang="ts">
import {
    Camera,
    CirclePlus,
    Gauge,
    Info,
    PiggyBank,
    Wallet,
    X,
    Zap,
} from '@lucide/vue';
import TopUpPresetCard from '@/components/credits/TopUpPresetCard.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { useBuyCreditsDialog } from '@/composables/useBuyCreditsDialog';
import type { TopUpPreset } from '@/types';

const { isOpen } = useBuyCreditsDialog();

const presets: TopUpPreset[] = [
    { amount: '$5.00', images: '~55 HQ images', caption: 'Starter rack' },
    {
        amount: '$10.00',
        images: '~120 HQ images',
        caption: 'Studio standard',
        badge: 'Most Popular',
        tone: 'primary',
        isSelected: true,
    },
    {
        amount: '$25.00',
        images: '~320 HQ images',
        caption: 'Best Value',
        badge: '+5% bonus',
        tone: 'tertiary',
    },
];
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent
            :show-close-button="false"
            class="gap-4 rounded-xl border-0 bg-surface-container-low p-6 shadow-2xl sm:max-w-lg"
        >
            <div
                class="pointer-events-none absolute -top-12 left-1/2 h-24 w-48 -translate-x-1/2 rounded-full bg-primary/10 blur-3xl"
            />

            <div class="flex flex-col gap-0.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <div
                            class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Wallet class="size-4.5" />
                        </div>
                        <DialogTitle class="text-headline-lg text-on-surface">
                            Buy Generation Credits
                        </DialogTitle>
                    </div>
                    <DialogClose
                        class="flex size-8 items-center justify-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
                    >
                        <X class="size-5" />
                        <span class="sr-only">Close</span>
                    </DialogClose>
                </div>
                <DialogDescription
                    class="pt-1 text-body-dense leading-relaxed text-on-surface-variant"
                >
                    Credits never expire and are billed per generation based on
                    exact token usage.
                </DialogDescription>

                <div
                    class="mt-1 flex items-center justify-between rounded-lg bg-surface-container p-2"
                >
                    <span
                        class="flex items-center gap-0.5 text-label-default text-on-surface-variant"
                    >
                        <PiggyBank class="size-4 text-outline" />
                        Current Balance
                    </span>
                    <span class="flex items-baseline gap-0.5">
                        <span
                            class="font-mono text-base font-semibold text-primary tabular-nums"
                        >
                            $4.2731
                        </span>
                        <span class="text-label-dense text-outline">USD</span>
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span
                        class="text-label-dense font-semibold tracking-wider text-outline uppercase"
                    >
                        Select Top-Up Amount
                    </span>
                    <span
                        class="flex items-center gap-0.5 text-label-dense text-tertiary"
                    >
                        <Zap class="size-3.5" />
                        Instant GPU Provisioning
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-1">
                    <TopUpPresetCard
                        v-for="preset in presets"
                        :key="preset.amount"
                        :preset="preset"
                    />
                </div>

                <div class="flex flex-col gap-0.5 pt-0.5">
                    <label
                        for="custom-amount"
                        class="flex items-center justify-between text-label-dense text-on-surface-variant"
                    >
                        <span>Or enter custom amount</span>
                        <span class="text-outline">USD Currency</span>
                    </label>
                    <div class="relative flex items-center">
                        <span
                            class="pointer-events-none absolute left-3 font-mono text-mono-metric text-on-surface-variant"
                        >
                            $
                        </span>
                        <input
                            id="custom-amount"
                            type="number"
                            min="2"
                            max="500"
                            step="1"
                            placeholder="e.g. 15.00"
                            class="w-full rounded-lg bg-surface-container-lowest py-1 pr-14 pl-7 font-mono text-mono-metric text-on-surface transition-all placeholder:text-outline focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                        <span
                            class="pointer-events-none absolute right-3 font-mono text-mono-badge text-outline"
                        >
                            USD
                        </span>
                    </div>
                    <span class="text-label-dense text-outline">
                        Minimum deposit: $2.00 • Maximum: $500.00
                    </span>
                </div>
            </div>

            <div
                class="flex flex-col gap-1 rounded-lg bg-surface-container p-2"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-label-dense font-semibold tracking-wider text-outline uppercase"
                    >
                        Estimated Capacity
                    </span>
                    <span
                        class="flex items-center gap-1 font-mono text-mono-badge text-on-surface-variant"
                    >
                        <span class="size-1.5 rounded-full bg-primary" />
                        Dynamic Ratio
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 py-0.5">
                    <div class="flex items-center gap-1">
                        <div
                            class="flex size-7 items-center justify-center rounded bg-surface-container-high text-primary"
                        >
                            <Camera class="size-4" />
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="font-mono text-mono-metric font-semibold text-on-surface tabular-nums"
                            >
                                ~120 images
                            </span>
                            <span class="text-label-dense text-outline">
                                1024×1024 High Quality
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <div
                            class="flex size-7 items-center justify-center rounded bg-surface-container-high text-tertiary"
                        >
                            <Gauge class="size-4" />
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="font-mono text-mono-metric font-semibold text-on-surface tabular-nums"
                            >
                                ~200 images
                            </span>
                            <span class="text-label-dense text-outline">
                                Fast 512×512 Draft renders
                            </span>
                        </div>
                    </div>
                </div>
                <div
                    class="mt-0.5 h-1 w-full overflow-hidden rounded-full bg-surface-container-highest"
                >
                    <div class="h-1 w-[45%] rounded-full bg-primary" />
                </div>
            </div>

            <div
                class="flex items-center gap-2 rounded-lg bg-surface-container-high/70 p-2"
            >
                <div
                    class="flex size-6 shrink-0 items-center justify-center rounded bg-amber-400/10 text-amber-400"
                >
                    <Info class="size-4" />
                </div>
                <div class="flex w-full items-center justify-between gap-2">
                    <span class="text-label-default text-on-surface-variant">
                        Demo only — no real payment or card charges will occur.
                    </span>
                    <span
                        class="shrink-0 rounded bg-amber-400/15 px-0.5 py-0.5 font-mono text-mono-badge text-amber-300 uppercase"
                    >
                        Sandbox
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-2 pt-0.5">
                <div class="flex items-center justify-between px-0.5">
                    <span class="text-label-default text-on-surface-variant">
                        New estimated balance
                    </span>
                    <span class="flex items-baseline gap-0.5">
                        <span
                            class="font-mono text-lg font-semibold text-on-surface tabular-nums"
                        >
                            $14.2731
                        </span>
                        <span class="font-mono text-mono-badge text-outline">
                            USD
                        </span>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <DialogClose
                        class="w-1/3 rounded-lg px-2 py-1 text-center text-label-default text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
                    >
                        Cancel
                    </DialogClose>
                    <button
                        type="button"
                        class="flex w-2/3 items-center justify-center gap-0.5 rounded-lg bg-primary px-2 py-1 text-label-default font-semibold text-primary-foreground shadow-md shadow-primary/20 transition-all hover:bg-primary-container active:scale-[0.98]"
                    >
                        <CirclePlus class="size-4.5" />
                        Confirm &amp; Add $10.00 Credits
                    </button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
