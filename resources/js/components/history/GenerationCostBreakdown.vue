<script setup lang="ts">
import type { CostBreakdownRow } from '@/types';

defineProps<{
    rows: CostBreakdownRow[];
    totalEstimated: string;
    totalActual: string;
    exactCharge: string;
    releasedHold: string;
}>();
</script>

<template>
    <div class="flex flex-col gap-1">
        <h3 class="text-headline-sm text-on-surface">Cost</h3>

        <div class="overflow-hidden rounded-lg bg-surface-container-lowest">
            <table class="w-full text-left text-body-dense">
                <thead>
                    <tr
                        class="bg-surface-container-high text-label-dense tracking-wider text-outline uppercase"
                    >
                        <th class="px-2 py-0.5 font-medium">Item</th>
                        <th class="px-2 py-0.5 text-right font-medium">
                            Estimate
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
                        <td
                            class="px-2 py-0.5 text-right whitespace-nowrap text-outline"
                        >
                            {{ row.estimated }}
                        </td>
                        <td
                            class="px-2 py-0.5 text-right text-on-surface tabular-nums"
                        >
                            {{ row.actual }}
                        </td>
                    </tr>
                    <tr
                        class="bg-surface-container-high/60 font-semibold text-on-surface"
                    >
                        <td class="px-2 py-1">Charged</td>
                        <td
                            class="px-2 py-1 text-right whitespace-nowrap text-outline"
                        >
                            {{ totalEstimated }}
                        </td>
                        <td
                            class="px-2 py-1 text-right text-primary tabular-nums"
                            :title="exactCharge"
                        >
                            {{ totalActual }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            class="flex items-center justify-between font-mono text-mono-badge text-outline"
        >
            <span>Exact: {{ exactCharge }}</span>
            <span class="text-green-400">{{ releasedHold }} hold released</span>
        </div>
    </div>
</template>
