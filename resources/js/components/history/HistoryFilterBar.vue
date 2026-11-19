<script setup lang="ts">
import type { HistoryFilterTab, HistoryStatusFilter } from '@/types';

defineProps<{
    tabs: HistoryFilterTab[];
}>();

const activeStatus = defineModel<HistoryStatusFilter>({ required: true });
</script>

<template>
    <div
        class="grid w-full grid-cols-2 gap-1 sm:flex sm:[scrollbar-width:none] sm:items-center sm:overflow-x-auto lg:w-auto lg:self-start [&::-webkit-scrollbar]:hidden"
        role="tablist"
        aria-label="Filter by status"
    >
        <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            role="tab"
            :aria-selected="activeStatus === tab.value"
            class="flex min-h-11 min-w-0 items-center justify-between gap-1 px-2 py-1 text-label-default whitespace-nowrap uppercase transition-colors sm:min-h-0 sm:justify-start"
            :class="
                activeStatus === tab.value
                    ? 'bg-primary font-bold text-primary-foreground shadow-pixel-sm'
                    : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface'
            "
            @click="activeStatus = tab.value"
        >
            [ {{ tab.label }}:
            <span class="font-mono tabular-nums">{{ tab.count }}</span>
            ]
        </button>
    </div>
</template>
