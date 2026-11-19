<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CloudOff, ImageIcon } from '@lucide/vue';
import { computed } from 'vue';
import showGeneration from '@/actions/App/Http/Controllers/ShowGenerationController';
import DownloadImageButton from '@/components/history/DownloadImageButton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { isFailedStatus } from '@/lib/generationSummary';
import type { GenerationSummary } from '@/types';

/**
 * One generation in the History list view: the same facts as a card, on a single line.
 */
const props = defineProps<{
    generation: GenerationSummary;
}>();

const isFailed = computed(() => isFailedStatus(props.generation.status));

const costClasses = computed(() => ({
    'text-primary': isFailed.value,
    'font-bold text-primary': props.generation.status === 'succeeded',
    'text-on-surface-variant':
        !isFailed.value && props.generation.status !== 'succeeded',
}));
</script>

<template>
    <article
        class="relative flex items-center gap-3 border-2 border-transparent bg-surface-raised p-2 shadow-pixel transition-colors hover:border-border-prominent"
    >
        <div
            class="flex size-14 shrink-0 items-center justify-center overflow-hidden bg-surface-container-lowest text-outline shadow-pixel-inset"
        >
            <img
                v-if="generation.imageUrl"
                :src="generation.imageUrl"
                :alt="generation.prompt"
                class="size-full object-cover"
            />
            <CloudOff v-else-if="generation.isExpired" class="size-5" />
            <ImageIcon v-else class="size-5" />
        </div>

        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p class="truncate text-body-dense text-on-surface">
                {{ generation.prompt }}
            </p>
            <div class="flex flex-wrap items-center gap-1">
                <StatusBadge
                    :status="generation.status"
                    :label="generation.statusLabel"
                />
                <span
                    v-for="tag in [generation.shape, generation.quality]"
                    :key="tag"
                    class="bg-surface-container-lowest px-1 py-0.5 font-mono text-mono-badge text-on-surface-variant uppercase"
                >
                    [ {{ tag }} ]
                </span>
                <span
                    v-if="isFailed && generation.errorMessage"
                    class="truncate text-label-dense text-error"
                >
                    {{ generation.errorMessage }}
                </span>
            </div>
            <div class="flex min-w-0 flex-col sm:hidden">
                <span
                    class="font-mono text-mono-metric tabular-nums"
                    :class="costClasses"
                >
                    {{ generation.cost }}
                </span>
                <span class="text-label-dense text-outline">
                    Est. {{ generation.estimate }} ·
                    {{ generation.createdAgo }}
                </span>
            </div>
        </div>

        <div class="hidden shrink-0 flex-col items-end sm:flex">
            <span class="text-label-dense text-outline">
                Est. {{ generation.estimate }}
            </span>
            <span
                class="font-mono text-mono-metric tabular-nums"
                :class="costClasses"
            >
                {{ generation.cost }}
            </span>
            <span class="text-label-dense text-outline md:hidden">
                {{ generation.createdAgo }}
            </span>
        </div>

        <span
            class="hidden w-24 shrink-0 text-right text-label-dense text-outline md:block"
        >
            {{ generation.createdAgo }}
        </span>

        <DownloadImageButton
            v-if="generation.canDownload"
            :generation-id="generation.id"
        />
        <span v-else class="size-11 shrink-0 sm:size-8" aria-hidden="true" />
        <!-- The whole row opens the generation; the download button sits above this link. -->
        <Link
            :href="showGeneration(generation.id)"
            class="absolute inset-0 z-10 focus-visible:ring-2 focus-visible:ring-primary focus-visible:outline-none"
        >
            <span class="sr-only">View generation</span>
        </Link>
    </article>
</template>
