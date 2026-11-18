<script setup lang="ts">
import type { CostBreakdownRow } from '@/types';

defineProps<{
    rows: CostBreakdownRow[];
    totalEstimated: string;
    totalActual: string;
    savedLabel: string;
}>();
</script>

<template>
    <div class="flex flex-col gap-1">
        <div class="flex items-center justify-between">
            <h3 class="text-headline-sm text-on-surface">Cost Breakdown</h3>
            <span
                class="rounded bg-green-500/10 px-0.5 py-0.5 font-mono text-mono-badge text-green-400"
            >
                {{ savedLabel }}
            </span>
        </div>

        <div class="overflow-hidden rounded-lg bg-surface-container-lowest">
            <table class="w-full text-left text-body-dense">
                <thead>
                    <tr
                        class="bg-surface-container-high text-label-dense tracking-wider text-outline uppercase"
                    >
                        <th class="px-2 py-0.5 font-medium">Inference Phase</th>
                        <th class="px-2 py-0.5 text-right font-medium">
                            Estimated
                        </th>
                        <th class="px-2 py-0.5 text-right font-medium">
                            Actual
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="font-mono text-mono-metric text-on-surface-variant"
                >
                    <tr
                        v-for="row in rows"
                        :key="row.phase"
                        class="hover:bg-surface-container/50"
                    >
                        <td class="px-2 py-0.5 text-on-surface">
                            {{ row.phase }}
                        </td>
                        <td class="px-2 py-0.5 text-right text-outline">
                            {{ row.estimated }}
                        </td>
                        <td
                            class="px-2 py-0.5 text-right tabular-nums"
                            :class="
                                row.isSaving
                                    ? 'text-green-400'
                                    : 'text-on-surface'
                            "
                        >
                            {{ row.actual }}
                        </td>
                    </tr>
                    <tr
                        class="bg-surface-container-high/60 font-semibold text-on-surface"
                    >
                        <td class="px-2 py-1">Total USD Charged</td>
                        <td class="px-2 py-1 text-right text-outline">
                            {{ totalEstimated }}
                        </td>
                        <td
                            class="px-2 py-1 text-right text-primary tabular-nums"
                        >
                            {{ totalActual }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
