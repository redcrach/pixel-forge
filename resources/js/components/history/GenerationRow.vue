<script setup lang="ts">
import { CloudOff, ImageIcon } from '@lucide/vue';
import { computed } from 'vue';
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
</script>

<template>
    <article
        class="relative flex items-center gap-3 rounded-lg border border-border-subtle bg-surface-raised p-2 transition-colors hover:border-border-prominent"
    >
        <div
            class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded border border-border-subtle bg-surface-container-lowest text-outline"
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
                    v-for="tag in [generation.size, generation.quality]"
                    :key="tag"
                    class="rounded-full border border-border-subtle bg-surface-container-lowest px-1.5 py-0.5 font-mono text-mono-badge text-on-surface-variant"
                >
                    {{ tag }}
                </span>
                <span
                    v-if="isFailed && generation.errorMessage"
                    class="truncate text-label-dense text-error"
                >
                    {{ generation.errorMessage }}
                </span>
            </div>
        </div>

        <div class="hidden shrink-0 flex-col items-end sm:flex">
            <span class="text-label-dense text-outline">
                Est. {{ generation.estimate }}
            </span>
            <span
                class="font-mono text-mono-metric tabular-nums"
                :class="{
                    'text-primary': isFailed,
                    'font-semibold text-on-surface':
                        generation.status === 'succeeded',
                    'text-on-surface-variant':
                        !isFailed && generation.status !== 'succeeded',
                }"
            >
                {{ generation.cost }}
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
        <span v-else class="size-8 shrink-0" aria-hidden="true" />
    </article>
</template>
