<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { CirclePlus, LoaderCircle, Wallet, X } from '@lucide/vue';
import { computed } from 'vue';
import buyCredits from '@/actions/App/Http/Controllers/BuyCreditsController';
import InputError from '@/components/InputError.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { useBuyCreditsDialog } from '@/composables/useBuyCreditsDialog';
import { dollarsToNano, formatMoney } from '@/lib/money';

const { isOpen } = useBuyCreditsDialog();
const page = usePage();

const presetAmounts = ['5', '10', '25'];

/** Simulated purchase: the server adds the credits, no payment is taken. */
const form = useForm({ amount: '10' });

const balanceNano = computed(() => page.props.balanceNano ?? 0);
const amountNano = computed(() => dollarsToNano(form.amount));
const newBalanceNano = computed(() =>
    amountNano.value === null ? null : balanceNano.value + amountNano.value,
);

function submit(): void {
    form.submit(buyCredits(), {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent
            :show-close-button="false"
            class="max-h-[calc(100svh-2rem)] overflow-y-auto rounded-none border-2 border-surface-container-highest bg-surface-container p-4 shadow-[8px_8px_0_#080b0a] sm:max-w-md sm:p-5"
        >
            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <div class="flex flex-col gap-0.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <div
                                class="flex size-9 items-center justify-center bg-surface-container-lowest text-primary shadow-pixel-inset"
                            >
                                <Wallet class="size-4.5" />
                            </div>
                            <DialogTitle
                                class="font-display text-headline-lg font-bold text-primary uppercase"
                            >
                                [ Insert coin ]
                            </DialogTitle>
                        </div>
                        <DialogClose
                            class="flex size-8 items-center justify-center bg-surface-container-high text-on-surface-variant transition-colors hover:bg-surface-container-highest hover:text-on-surface"
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
                    class="flex items-center justify-between bg-surface-container-lowest p-3 shadow-pixel-inset"
                >
                    <span
                        class="text-label-default text-on-surface-variant uppercase"
                    >
                        Current balance
                    </span>
                    <span
                        class="font-mono text-base font-bold tracking-wider text-wheat tabular-nums"
                    >
                        {{ formatMoney(balanceNano) }}
                    </span>
                </div>

                <div class="flex flex-col gap-2">
                    <span
                        class="font-mono text-label-default font-bold tracking-[0.08em] text-on-surface uppercase"
                    >
                        &#9654; Select top-up amount
                    </span>

                    <div class="grid grid-cols-3 gap-1">
                        <button
                            v-for="presetAmount in presetAmounts"
                            :key="presetAmount"
                            type="button"
                            class="py-3 font-display text-headline-sm font-bold transition-colors"
                            :class="
                                form.amount === presetAmount
                                    ? 'bg-primary text-primary-foreground shadow-pixel-inset'
                                    : 'bg-surface-container-low text-on-surface shadow-pixel-sm hover:bg-surface-container-high'
                            "
                            @click="form.amount = presetAmount"
                        >
                            [ ${{ presetAmount }} ]
                        </button>
                    </div>

                    <div class="flex flex-col gap-0.5">
                        <label
                            for="custom-amount"
                            class="text-label-dense text-on-surface-variant uppercase"
                        >
                            Or enter a custom amount
                        </label>
                        <div class="relative flex items-center">
                            <span
                                class="pointer-events-none absolute left-3 font-mono text-mono-metric font-bold text-primary"
                            >
                                $
                            </span>
                            <input
                                id="custom-amount"
                                v-model="form.amount"
                                type="text"
                                inputmode="decimal"
                                autocomplete="off"
                                placeholder="15.00"
                                class="w-full border-2 border-transparent bg-surface-container-lowest py-2 pr-2 pl-7 font-mono text-base font-bold text-primary shadow-pixel-inset transition-colors placeholder:text-outline focus:border-primary focus:outline-none sm:text-sm"
                            />
                        </div>
                        <span class="text-label-dense text-outline uppercase">
                            [ Min: $2.00 | Max: $500.00 ]
                        </span>
                        <InputError :message="form.errors.amount" />
                    </div>
                </div>

                <div
                    class="flex items-center gap-2 bg-surface-container-lowest p-2 text-label-default text-on-surface uppercase"
                >
                    <span
                        class="shrink-0 bg-error-container px-1.5 py-0.5 font-bold text-error"
                    >
                        [ Sandbox ]
                    </span>
                    Demo mode: no real payment is taken.
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-label-default text-on-surface-variant uppercase"
                        >
                            [ New balance ]
                        </span>
                        <span
                            class="font-display text-headline-lg font-bold text-primary tabular-nums"
                        >
                            {{
                                newBalanceNano === null
                                    ? '—'
                                    : formatMoney(newBalanceNano)
                            }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <DialogClose
                            class="pixel-press w-1/3 bg-surface-container-high px-2 py-2 text-center text-label-default font-bold text-on-surface uppercase transition-colors hover:bg-surface-container-highest"
                        >
                            [ Cancel ]
                        </DialogClose>
                        <button
                            type="submit"
                            :disabled="amountNano === null || form.processing"
                            class="pixel-press flex w-2/3 items-center justify-center gap-1 bg-primary px-2 py-2 text-label-default font-bold text-primary-foreground uppercase transition-colors hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50"
                        >
                            <LoaderCircle
                                v-if="form.processing"
                                class="size-4.5 animate-spin"
                            />
                            <CirclePlus v-else class="size-4.5" />
                            {{
                                amountNano === null
                                    ? '[ Add credits ]'
                                    : `[ Insert +${formatMoney(amountNano, 2)} ]`
                            }}
                        </button>
                    </div>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
