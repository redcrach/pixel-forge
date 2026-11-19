<script setup lang="ts">
import { Head, InfiniteScroll, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';
import EmptyHistory from '@/components/history/EmptyHistory.vue';
import GenerationCard from '@/components/history/GenerationCard.vue';
import {
    GENERATION_UPDATED_EVENT,
    generationChannel,
} from '@/lib/generationChannel';
import { toGenerationSummary } from '@/lib/generationSummary';
import type {
    GenerationHistoryItem,
    GenerationUpdatedPayload,
    HistoryPageProps,
} from '@/types';

const props = defineProps<HistoryPageProps>();
const page = usePage();

const generationSummaries = computed(() =>
    props.generations.data.map(toGenerationSummary),
);

/**
 * A pushed generation replaces its card in place, or appears at the top when it is new.
 * Patched locally: reloading the list would throw away the pages already scrolled.
 */
function applyPushedGeneration(
    generations: GenerationHistoryItem[],
    pushedGeneration: GenerationHistoryItem,
): GenerationHistoryItem[] {
    const isAlreadyListed = generations.some(
        (generation) => generation.id === pushedGeneration.id,
    );

    if (!isAlreadyListed) {
        return [pushedGeneration, ...generations];
    }

    return generations.map((generation) =>
        generation.id === pushedGeneration.id ? pushedGeneration : generation,
    );
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
        <div class="flex flex-col gap-0.5">
            <h1 class="text-headline-xl tracking-tight text-on-surface">
                History
            </h1>
            <p class="text-body-default text-on-surface-variant">
                Your generations, their status and what they cost.
            </p>
        </div>

        <EmptyHistory v-if="generationSummaries.length === 0" />

        <!-- Newest first: only older pages load as you scroll, and the URL stays /history. -->
        <InfiniteScroll
            v-else
            data="generations"
            only-next
            preserve-url
            items-element="#generation-grid"
        >
            <div
                id="generation-grid"
                class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3"
            >
                <GenerationCard
                    v-for="generation in generationSummaries"
                    :key="generation.id"
                    :generation="generation"
                />
            </div>

            <template #loading>
                <div
                    class="flex items-center justify-center gap-1 py-3 text-label-default text-on-surface-variant"
                >
                    <LoaderCircle class="size-4 animate-spin" />
                    Loading older generations…
                </div>
            </template>
        </InfiniteScroll>
    </div>
</template>
