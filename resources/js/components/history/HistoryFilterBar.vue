<script setup lang="ts">
import type { HistoryFilterTab } from '@/types';

defineProps<{
    tabs: HistoryFilterTab[];
}>();
</script>

<template>
    <div
        class="flex items-center gap-0.5 self-start overflow-x-auto rounded-lg bg-surface-container-lowest p-0.5"
    >
        <button
            v-for="tab in tabs"
            :key="tab.label"
            type="button"
            class="flex items-center gap-0.5 rounded-md px-2 py-0.5 text-label-default whitespace-nowrap transition-all"
            :class="
                tab.isActive
                    ? 'bg-surface-container-high text-on-surface'
                    : 'text-on-surface-variant hover:text-on-surface'
            "
        >
            {{ tab.label }}
            <span
                class="rounded px-1 font-mono text-mono-badge"
                :class="
                    tab.isActive
                        ? 'bg-surface-container-highest text-primary'
                        : 'bg-surface-container-high text-outline'
                "
            >
                {{ tab.count }}
            </span>
        </button>
    </div>
</template>
