<script setup lang="ts">
import { CirclePlus, Info, Wallet, X } from '@lucide/vue';
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
    { amount: '$5.00' },
    { amount: '$10.00', isSelected: true },
    { amount: '$25.00' },
];
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent
            :show-close-button="false"
            class="gap-4 rounded-xl border-0 bg-surface-container-low p-6 shadow-2xl sm:max-w-md"
        >
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <div
                            class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Wallet class="size-4.5" />
                        </div>
                        <DialogTitle class="text-headline-lg text-on-surface">
                            Buy credits
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
                    Credits are US dollars. Each generation is charged its
                    actual cost when it completes.
                </DialogDescription>
            </div>

            <div
                class="flex items-center justify-between rounded-lg bg-surface-container p-2"
            >
                <span class="text-label-default text-on-surface-variant">
                    Current balance
                </span>
                <span
                    class="font-mono text-base font-semibold text-primary tabular-nums"
                >
                    $4.2731
                </span>
            </div>

            <div class="flex flex-col gap-2">
                <span
                    class="text-label-dense font-semibold tracking-wider text-outline uppercase"
                >
                    Amount
                </span>

                <div class="grid grid-cols-3 gap-1">
                    <button
                        v-for="preset in presets"
                        :key="preset.amount"
                        type="button"
                        class="rounded-lg py-3 font-mono text-base font-semibold transition-all"
                        :class="
                            preset.isSelected
                                ? 'bg-surface-container-high text-primary ring-1 ring-primary'
                                : 'bg-surface-container text-on-surface hover:bg-surface-container-high'
                        "
                    >
                        {{ preset.amount }}
                    </button>
                </div>

                <div class="flex flex-col gap-0.5">
                    <label
                        for="custom-amount"
                        class="text-label-dense text-on-surface-variant"
                    >
                        Or enter a custom amount
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
                            placeholder="15.00"
                            class="w-full rounded-lg bg-surface-container-lowest py-1 pr-2 pl-7 font-mono text-mono-metric text-on-surface transition-all placeholder:text-outline focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                    </div>
                    <span class="text-label-dense text-outline">
                        Between $2.00 and $500.00
                    </span>
                </div>
            </div>

            <div
                class="flex items-center gap-2 rounded-lg bg-amber-400/10 p-2 text-label-default text-amber-300"
            >
                <Info class="size-4 shrink-0" />
                Demo only — no real payment is taken.
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-label-default text-on-surface-variant">
                        New balance
                    </span>
                    <span
                        class="font-mono text-lg font-semibold text-on-surface tabular-nums"
                    >
                        $14.2731
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
                        Add $10.00
                    </button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
