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
    <div
        class="flex flex-col gap-2 rounded-lg border border-border-subtle bg-surface-raised p-2 lg:flex-row lg:items-center"
    >
        <HistoryFilterBar v-model="status" :tabs="tabs" />

        <div class="flex flex-1 items-center gap-2">
            <label class="relative flex-1">
                <span class="sr-only">Search prompts</span>
                <Search
                    class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-outline"
                />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search prompts…"
                    class="w-full rounded border border-border-subtle bg-[#141414] py-1.5 pr-2 pl-8 text-body-dense text-on-surface placeholder:text-outline focus:border-primary focus:outline-none"
                />
            </label>

            <label class="shrink-0">
                <span class="sr-only">Sort</span>
                <select
                    v-model="isDescending"
                    class="rounded border border-border-subtle bg-[#141414] py-1.5 pr-7 pl-2 font-mono text-label-default text-on-surface focus:border-primary focus:outline-none"
                >
                    <option :value="true">Newest first</option>
                    <option :value="false">Oldest first</option>
                </select>
            </label>

            <div
                class="flex shrink-0 items-center gap-0.5 rounded border border-border-subtle bg-surface-container-lowest p-0.5"
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
                    class="flex size-7 items-center justify-center rounded transition-colors"
                    :class="
                        viewMode === mode.value
                            ? 'bg-primary/10 text-primary'
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
