<script setup lang="ts">
import { LayoutGrid, Rows3, Search } from '@lucide/vue';
import type { HistoryFilterTab } from '@/types';

defineProps<{
    tabs: HistoryFilterTab[];
}>();

const countToneClasses: Record<HistoryFilterTab['tone'], string> = {
    primary: 'text-primary',
    processing: 'text-amber-400',
    succeeded: 'text-green-400',
    failed: 'text-rose-400',
};
</script>

<template>
    <div
        class="flex flex-col items-stretch justify-between gap-2 rounded-xl bg-surface-container-low p-0.5 lg:flex-row lg:items-center"
    >
        <div
            class="flex items-center gap-0.5 overflow-x-auto rounded-lg bg-surface-container-lowest p-0.5"
        >
            <button
                v-for="tab in tabs"
                :key="tab.label"
                type="button"
                class="flex items-center gap-0.5 rounded-md px-2 py-0.5 text-label-default transition-all"
                :class="
                    tab.isActive
                        ? 'bg-surface-container-high text-on-surface'
                        : 'text-on-surface-variant hover:text-on-surface'
                "
            >
                {{ tab.label }}
                <span
                    class="rounded px-1 font-mono text-mono-badge"
                    :class="[
                        countToneClasses[tab.tone],
                        tab.isActive
                            ? 'bg-surface-container-highest'
                            : 'bg-surface-container-high',
                    ]"
                >
                    {{ tab.count }}
                </span>
            </button>
        </div>

        <div class="flex max-w-lg flex-1 items-center gap-1 lg:justify-end">
            <div class="relative flex-1">
                <Search
                    class="absolute top-1/2 left-2.5 size-4.5 -translate-y-1/2 text-outline"
                />
                <input
                    type="text"
                    placeholder="Filter by prompt keyword or job ID..."
                    class="w-full rounded-lg bg-surface-container-lowest py-0.5 pr-2 pl-9 text-body-dense text-on-surface outline-none placeholder:text-outline focus:ring-1 focus:ring-primary"
                />
            </div>
            <div
                class="flex items-center rounded-lg bg-surface-container-lowest p-0.5"
            >
                <button
                    type="button"
                    title="Grid View"
                    class="rounded bg-surface-container-high p-0.5 text-primary"
                >
                    <LayoutGrid class="size-4.5" />
                </button>
                <button
                    type="button"
                    title="Table View"
                    class="rounded p-0.5 text-outline hover:text-on-surface"
                >
                    <Rows3 class="size-4.5" />
                </button>
            </div>
        </div>
    </div>
</template>
