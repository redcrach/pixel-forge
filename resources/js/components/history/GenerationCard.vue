<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CloudOff, Hourglass, ListOrdered, LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';
import showGeneration from '@/actions/App/Http/Controllers/ShowGenerationController';
import DownloadImageButton from '@/components/history/DownloadImageButton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { isFailedStatus } from '@/lib/generationSummary';
import type { GenerationSummary } from '@/types';

const props = defineProps<{
    generation: GenerationSummary;
    isSelected?: boolean;
}>();

const isFailed = computed(() => isFailedStatus(props.generation.status));
const isFinished = computed(
    () => isFailed.value || props.generation.status === 'succeeded',
);
</script>

<template>
    <article
        class="group relative flex cursor-pointer flex-col overflow-hidden rounded-lg border transition-all"
        :class="
            isSelected
                ? 'border-primary bg-surface-raised shadow-[0_0_12px_rgba(154,230,0,0.25)]'
                : 'border-border-subtle bg-surface-raised hover:border-border-prominent'
        "
    >
        <div
            class="relative flex aspect-video w-full items-center justify-center overflow-hidden border-b border-border-subtle bg-surface-container-lowest"
        >
            <template v-if="generation.imageUrl">
                <img
                    :src="generation.imageUrl"
                    :alt="generation.prompt"
                    class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div
                    class="absolute inset-0 bg-linear-to-t from-surface-container-lowest via-transparent to-transparent opacity-80"
                />
            </template>

            <div
                v-else-if="generation.status === 'processing'"
                class="flex flex-col items-center gap-1"
            >
                <div
                    class="absolute inset-0 animate-shimmer bg-linear-to-r from-transparent via-surface-container-high/40 to-transparent"
                />
                <div
                    class="relative flex size-9 items-center justify-center rounded-full border border-amber-400/30 bg-amber-400/10"
                >
                    <LoaderCircle class="size-5 animate-spin text-amber-400" />
                </div>
                <StatusBadge
                    class="relative"
                    :status="generation.status"
                    :label="generation.statusLabel"
                />
            </div>

            <div
                v-else-if="generation.status === 'pending'"
                class="flex flex-col items-center gap-1"
                title="Waiting for a free slot, it will be sent automatically"
            >
                <div
                    class="flex size-9 items-center justify-center rounded-full border border-border-prominent bg-surface-container-high text-outline"
                >
                    <Hourglass class="size-5" />
                </div>
                <StatusBadge
                    :status="generation.status"
                    :label="generation.statusLabel"
                />
            </div>

            <div
                v-else-if="generation.status === 'queued'"
                class="flex flex-col items-center gap-1"
            >
                <div
                    class="flex size-9 items-center justify-center rounded-full border border-tertiary/30 bg-tertiary/10 text-tertiary"
                >
                    <ListOrdered class="size-5" />
                </div>
                <StatusBadge
                    :status="generation.status"
                    :label="generation.statusLabel"
                />
            </div>

            <div
                v-else-if="generation.isExpired"
                class="flex flex-col items-center p-2 text-center"
            >
                <div
                    class="mb-0.5 flex size-9 items-center justify-center rounded-full bg-surface-container-high text-outline"
                >
                    <CloudOff class="size-5" />
                </div>
                <span class="text-body-dense font-medium text-on-surface">
                    Image expired
                </span>
            </div>

            <template v-else-if="isFailed">
                <div
                    class="absolute inset-0 bg-[repeating-linear-gradient(45deg,var(--color-error),var(--color-error)_10px,transparent_10px,transparent_20px)] opacity-10"
                />
                <div
                    class="relative flex flex-col items-center gap-0.5 p-2 text-center"
                >
                    <span
                        class="font-mono text-label-default font-semibold tracking-[0.04em] text-error uppercase"
                    >
                        {{ generation.errorMessage }}
                    </span>
                    <span class="font-mono text-mono-badge text-outline">
                        {{ generation.errorCode }}
                    </span>
                </div>
            </template>

            <StatusBadge
                v-if="isFinished"
                class="absolute top-2 right-2 backdrop-blur-sm"
                :status="generation.status"
                :label="generation.statusLabel"
            />
        </div>

        <div class="flex flex-1 flex-col justify-between gap-3 p-3">
            <div class="flex flex-col gap-0.5">
                <p class="line-clamp-2 text-body-dense text-on-surface">
                    {{ generation.prompt }}
                </p>
                <div class="flex flex-wrap items-center gap-0.5 pt-0.5">
                    <span
                        v-for="tag in [generation.size, generation.quality]"
                        :key="tag"
                        class="rounded-full border border-border-subtle bg-surface-container-lowest px-1.5 py-0.5 font-mono text-mono-badge text-on-surface-variant"
                    >
                        {{ tag }}
                    </span>
                </div>
            </div>

            <div
                class="flex items-center justify-between border-t border-border-subtle pt-2"
            >
                <div class="flex flex-col">
                    <span class="text-label-dense text-outline">
                        Est. {{ generation.estimate }}
                    </span>
                    <span
                        class="font-mono text-mono-metric tabular-nums"
                        :class="{
                            'text-primary': isFailed,
                            'font-semibold text-on-surface':
                                generation.status === 'succeeded',
                            'text-on-surface-variant': !isFinished,
                        }"
                    >
                        {{ generation.cost }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-label-dense text-outline">
                        {{ generation.createdAgo }}
                    </span>
                    <DownloadImageButton
                        v-if="generation.canDownload"
                        :generation-id="generation.id"
                    />
                </div>
            </div>
        </div>
        <!-- The whole card opens the generation; the download button sits above this link. -->
        <Link
            :href="showGeneration(generation.id)"
            class="absolute inset-0 z-10 rounded-lg focus-visible:ring-2 focus-visible:ring-primary focus-visible:outline-none"
        >
            <span class="sr-only">View generation</span>
        </Link>
    </article>
</template>
