<script setup lang="ts">
import { Head, InfiniteScroll, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { LoaderCircle, SearchX } from '@lucide/vue';
import { useStorage, watchDebounced } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import EmptyHistory from '@/components/history/EmptyHistory.vue';
import GenerationCard from '@/components/history/GenerationCard.vue';
import GenerationRow from '@/components/history/GenerationRow.vue';
import HistoryToolbar from '@/components/history/HistoryToolbar.vue';
import {
    GENERATION_UPDATED_EVENT,
    generationChannel,
} from '@/lib/generationChannel';
import {
    matchesHistoryFilters,
    toGenerationSummary,
} from '@/lib/generationSummary';
import { history } from '@/routes';
import type {
    GenerationHistoryItem,
    GenerationUpdatedPayload,
    HistoryFilterTab,
    HistoryPageProps,
    HistoryViewMode,
} from '@/types';

const props = defineProps<HistoryPageProps>();
const page = usePage();

const generationSummaries = computed(() =>
    props.generations.data.map(toGenerationSummary),
);

const status = ref(props.filters.status);
const search = ref(props.filters.search);
const isDescending = ref(props.filters.is_desc);

/** A per-browser preference, so it lives in localStorage rather than on the server. */
const viewMode = useStorage<HistoryViewMode>('pixelforge.history.view', 'grid');

const tabs = computed<HistoryFilterTab[]>(() => [
    { value: 'all', label: 'All', count: props.counts.all },
    {
        value: 'in_progress',
        label: 'In progress',
        count: props.counts.in_progress,
    },
    { value: 'succeeded', label: 'Succeeded', count: props.counts.succeeded },
    { value: 'failed', label: 'Failed', count: props.counts.failed },
]);

const hasActiveFilters = computed(
    () => props.filters.status !== 'all' || props.filters.search !== '',
);

/**
 * The server filters and sorts, so infinite scroll keeps working. Defaults are left out of the URL,
 * and the scroll prop is reset so pagination starts again from the first page.
 */
function applyFilters(): void {
    router.get(
        history.url(),
        {
            status: status.value === 'all' ? undefined : status.value,
            search: search.value.trim() || undefined,
            is_desc: isDescending.value ? undefined : 0,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            reset: ['generations'],
        },
    );
}

watch([status, isDescending], applyFilters);
watchDebounced(search, applyFilters, { debounce: 300 });

/**
 * A pushed generation replaces its card in place, appears at the top when it is new,
 * or leaves the list when it no longer matches the current tab or search.
 * Patched locally: reloading the list would throw away the pages already scrolled.
 */
function applyPushedGeneration(
    generations: GenerationHistoryItem[],
    pushedGeneration: GenerationHistoryItem,
): GenerationHistoryItem[] {
    const otherGenerations = generations.filter(
        (generation) => generation.id !== pushedGeneration.id,
    );
    const isAlreadyListed = otherGenerations.length !== generations.length;

    if (!matchesHistoryFilters(pushedGeneration, props.filters)) {
        return otherGenerations;
    }

    if (isAlreadyListed) {
        return generations.map((generation) =>
            generation.id === pushedGeneration.id
                ? pushedGeneration
                : generation,
        );
    }

    // Oldest first: a new generation belongs at the very end, where scrolling will reach it.
    if (!props.filters.is_desc) {
        return generations;
    }

    return [pushedGeneration, ...generations];
}

useEcho<GenerationUpdatedPayload>(
    generationChannel(page.props.auth.user!.id),
    GENERATION_UPDATED_EVENT,
    ({ generation }) => {
        router.replaceProp('generations.data', (generations: unknown) =>
            applyPushedGeneration(
                generations as GenerationHistoryItem[],
                generation,
            ),
        );
    },
);
</script>

<template>
    <Head title="History" />

    <div class="flex flex-col gap-4">
        <div
            class="flex flex-col gap-1 bg-surface-container-low p-4 shadow-pixel"
        >
            <span
                class="font-mono text-label-default font-bold tracking-wider text-primary uppercase"
            >
                Archive_node // History
            </span>
            <h1
                class="font-display text-headline-lg font-bold text-on-surface uppercase sm:text-headline-xl"
            >
                Generation logs &amp; archive
            </h1>
            <p class="text-body-default text-on-surface-variant">
                Your generations, their status and what they cost.
            </p>
        </div>

        <EmptyHistory v-if="props.counts.all === 0 && !hasActiveFilters" />

        <template v-else>
            <HistoryToolbar
                v-model:status="status"
                v-model:search="search"
                v-model:is-descending="isDescending"
                v-model:view-mode="viewMode"
                :tabs="tabs"
            />

            <div
                v-if="generationSummaries.length === 0"
                class="flex flex-col items-center gap-1 border-2 border-border-subtle bg-surface-raised px-6 py-10 text-center shadow-pixel"
            >
                <SearchX class="size-6 text-outline" />
                <p class="text-body-default text-on-surface uppercase">
                    No generations match these filters.
                </p>
            </div>

            <!-- Only older pages load as you scroll; the URL keeps the filters but not the cursor. -->
            <InfiniteScroll
                v-else
                data="generations"
                only-next
                preserve-url
                items-element="#generation-items"
            >
                <div
                    v-if="viewMode === 'grid'"
                    id="generation-items"
                    class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3"
                >
                    <GenerationCard
                        v-for="generation in generationSummaries"
                        :key="generation.id"
                        :generation="generation"
                    />
                </div>

                <div v-else id="generation-items" class="flex flex-col gap-2">
                    <GenerationRow
                        v-for="generation in generationSummaries"
                        :key="generation.id"
                        :generation="generation"
                    />
                </div>

                <template #loading>
                    <div
                        class="flex items-center justify-center gap-1 py-3 text-label-default text-wheat uppercase"
                    >
                        <LoaderCircle class="size-4 animate-spin" />
                        Loading older generations…
                    </div>
                </template>
            </InfiniteScroll>
        </template>
    </div>
</template>
