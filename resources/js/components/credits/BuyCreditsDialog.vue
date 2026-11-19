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
            class="max-h-[calc(100svh-2rem)] gap-4 overflow-y-auto rounded-lg border border-border-prominent bg-[#202020] p-4 shadow-[0_8px_24px_rgba(0,0,0,0.65)] sm:max-w-md sm:p-6"
        >
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <div
                            class="flex size-9 items-center justify-center rounded border border-primary/30 bg-primary/10 text-primary"
                        >
                            <Wallet class="size-4.5" />
                        </div>
                        <DialogTitle
                            class="font-display text-headline-lg text-on-surface"
                        >
                            Buy credits
                        </DialogTitle>
                    </div>
                    <DialogClose
                        class="flex size-8 items-center justify-center rounded text-[#a3a3a3] transition-colors hover:bg-surface-container-high hover:text-canvas-light"
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
                class="flex items-center justify-between rounded-lg border border-border-subtle bg-surface-raised p-3"
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
                    class="font-mono text-label-default font-semibold tracking-[0.04em] text-on-surface uppercase"
                >
                    Amount
                </span>

                <div class="grid grid-cols-3 gap-1">
                    <button
                        v-for="preset in presets"
                        :key="preset.amount"
                        type="button"
                        class="rounded-lg border py-3 font-display text-headline-sm font-bold transition-all"
                        :class="
                            preset.isSelected
                                ? 'border-2 border-primary bg-surface-raised text-primary shadow-[0_0_12px_rgba(154,230,0,0.25)]'
                                : 'border-border-subtle bg-surface-raised text-on-surface hover:border-border-prominent'
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
                            class="w-full rounded border border-border-subtle bg-[#141414] py-2 pr-2 pl-7 font-mono text-base text-on-surface transition-colors placeholder:text-outline focus:border-primary focus:outline-none sm:text-sm"
                        />
                    </div>
                    <span class="text-label-dense text-outline">
                        Between $2.00 and $500.00
                    </span>
                </div>
            </div>

            <div
                class="flex items-center gap-2 rounded-lg border border-amber-400/30 bg-amber-400/10 p-3 text-label-default text-amber-300"
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
                        class="font-display text-headline-lg text-on-surface tabular-nums"
                    >
                        $14.2731
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <DialogClose
                        class="w-1/3 rounded border border-border-prominent bg-surface-raised px-2 py-2 text-center text-label-default font-semibold text-canvas-light transition-colors hover:border-[#4a4a4a] hover:bg-[#242424]"
                    >
                        Cancel
                    </DialogClose>
                    <button
                        type="button"
                        class="flex w-2/3 items-center justify-center gap-1 rounded bg-primary px-2 py-2 text-label-default font-bold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)] transition-all hover:bg-primary-container active:scale-[0.98]"
                    >
                        <CirclePlus class="size-4.5" />
                        Add $10.00
                    </button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
