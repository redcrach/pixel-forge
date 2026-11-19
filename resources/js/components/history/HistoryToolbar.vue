<script setup lang="ts">
import { LayoutGrid, List, Search } from '@lucide/vue';
import HistoryFilterBar from '@/components/history/HistoryFilterBar.vue';
import type {
    HistoryFilterTab,
    HistoryStatusFilter,
    HistoryViewMode,
} from '@/types';

/**
 * Status tabs, prompt search, sort order and the grid/list switch above the History list.
 */
defineProps<{
    tabs: HistoryFilterTab[];
}>();

const status = defineModel<HistoryStatusFilter>('status', { required: true });
const search = defineModel<string>('search', { required: true });
const isDescending = defineModel<boolean>('isDescending', { required: true });
const viewMode = defineModel<HistoryViewMode>('viewMode', { required: true });

const viewModes: {
    value: HistoryViewMode;
    label: string;
    icon: typeof List;
}[] = [
    { value: 'grid', label: 'Grid view', icon: LayoutGrid },
    { value: 'list', label: 'List view', icon: List },
];
</script>

<template>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <HistoryFilterBar v-model="status" :tabs="tabs" />

        <div class="flex flex-1 flex-wrap items-center gap-2 sm:flex-nowrap">
            <label class="relative w-full sm:w-auto sm:flex-1">
                <span class="sr-only">Search prompts</span>
                <Search
                    class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-outline"
                />
                <input
                    v-model="search"
                    type="search"
                    placeholder="[ Find prompt / keyword… ]"
                    class="min-h-11 w-full border-2 border-surface-container-highest bg-surface-container-lowest py-1.5 pr-2 pl-8 text-base text-primary placeholder:text-outline placeholder:uppercase focus:border-primary focus:outline-none sm:min-h-0 sm:text-sm"
                />
            </label>

            <label class="flex-1 sm:flex-none">
                <span class="sr-only">Sort</span>
                <select
                    v-model="isDescending"
                    class="min-h-11 w-full border-2 border-surface-container-highest bg-surface-container-lowest py-1.5 pr-7 pl-2 font-mono text-base text-on-surface uppercase focus:border-primary focus:outline-none sm:min-h-0 sm:text-sm"
                >
                    <option :value="true">Newest first</option>
                    <option :value="false">Oldest first</option>
                </select>
            </label>

            <div
                class="flex shrink-0 items-center gap-0.5 border-2 border-surface-container-highest bg-surface-container-lowest p-0.5"
                role="radiogroup"
                aria-label="Layout"
            >
                <button
                    v-for="mode in viewModes"
                    :key="mode.value"
                    type="button"
                    role="radio"
                    :aria-checked="viewMode === mode.value"
                    :title="mode.label"
                    class="flex size-11 items-center justify-center transition-colors sm:size-7"
                    :class="
                        viewMode === mode.value
                            ? 'bg-primary text-primary-foreground'
                            : 'text-outline hover:text-on-surface'
                    "
                    @click="viewMode = mode.value"
                >
                    <component :is="mode.icon" class="size-4" />
                    <span class="sr-only">{{ mode.label }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
