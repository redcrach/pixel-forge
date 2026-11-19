<script setup lang="ts">
import type { HistoryFilterTab, HistoryStatusFilter } from '@/types';

defineProps<{
    tabs: HistoryFilterTab[];
}>();

const activeStatus = defineModel<HistoryStatusFilter>({ required: true });
</script>

<template>
    <div
        class="flex items-center gap-0.5 self-start overflow-x-auto rounded-lg border border-border-subtle bg-surface-container-lowest p-0.5"
        role="tablist"
        aria-label="Filter by status"
    >
        <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            role="tab"
            :aria-selected="activeStatus === tab.value"
            class="flex items-center gap-1 rounded border px-2 py-1 text-label-default whitespace-nowrap transition-all"
            :class="
                activeStatus === tab.value
                    ? 'border-primary/40 bg-primary/10 font-semibold text-primary'
                    : 'border-transparent text-on-surface-variant hover:text-on-surface'
            "
            @click="activeStatus = tab.value"
        >
            {{ tab.label }}
            <span
                class="rounded-full px-1.5 font-mono text-mono-badge tabular-nums"
                :class="
                    activeStatus === tab.value
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-surface-container-high text-outline'
                "
            >
                {{ tab.count }}
            </span>
        </button>
    </div>
</template>
