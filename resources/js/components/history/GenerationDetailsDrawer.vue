<script setup lang="ts">
import { Copy, Download, X } from '@lucide/vue';
import GenerationCostBreakdown from '@/components/history/GenerationCostBreakdown.vue';
import GenerationTimeline from '@/components/history/GenerationTimeline.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { GenerationDetails } from '@/types';

defineProps<{
    generation: GenerationDetails;
}>();

defineEmits<{
    close: [];
}>();
</script>

<template>
    <aside
        class="flex w-full shrink-0 flex-col overflow-hidden rounded-xl bg-surface-container-low shadow-2xl xl:sticky xl:top-16 xl:w-[420px]"
    >
        <div
            class="flex items-center justify-between bg-surface-container-high px-4 py-2"
        >
            <div class="flex flex-col">
                <div class="flex items-center gap-1">
                    <h2 class="text-headline-sm text-on-surface">
                        Generation #{{ generation.id }}
                    </h2>
                    <StatusBadge
                        :status="generation.status"
                        :label="generation.statusLabel"
                    />
                </div>
                <span class="text-label-dense text-outline">
                    {{ generation.createdAt }}
                </span>
            </div>
            <button
                type="button"
                class="flex size-7 items-center justify-center rounded text-outline transition-colors hover:bg-surface-container hover:text-on-surface"
                @click="$emit('close')"
            >
                <X class="size-4.5" />
                <span class="sr-only">Close details</span>
            </button>
        </div>

        <div
            class="flex flex-col gap-4 p-4 xl:max-h-[calc(100vh-140px)] xl:overflow-y-auto"
        >
            <div class="flex flex-col gap-1">
                <div
                    class="relative aspect-square w-full overflow-hidden rounded-lg bg-surface-container-lowest"
                >
                    <img
                        :src="generation.imageUrl"
                        :alt="generation.prompt"
                        class="size-full object-cover"
                    />
                    <span
                        class="absolute right-2 bottom-2 rounded bg-surface-container-lowest/80 px-0.5 py-0.5 font-mono text-mono-badge text-on-surface backdrop-blur"
                    >
                        {{ generation.imageMeta }}
                    </span>
                </div>

                <button
                    type="button"
                    class="flex items-center justify-center gap-0.5 rounded-lg bg-primary px-1 py-1 text-label-default text-primary-foreground transition-colors hover:bg-primary-container"
                >
                    <Download class="size-4" />
                    Download
                </button>
            </div>

            <div
                class="flex flex-col gap-0.5 rounded-lg bg-surface-container-lowest p-2"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-label-dense tracking-wider text-outline uppercase"
                    >
                        Prompt
                    </span>
                    <button
                        type="button"
                        class="flex items-center gap-1 text-label-dense text-outline transition-colors hover:text-primary"
                    >
                        <Copy class="size-3.5" />
                        Copy
                    </button>
                </div>
                <p
                    class="text-body-dense leading-relaxed text-on-surface select-all"
                >
                    {{ generation.prompt }}
                </p>
                <span class="pt-0.5 font-mono text-mono-badge text-outline">
                    {{ generation.settings }}
                </span>
            </div>

            <GenerationCostBreakdown
                :rows="generation.costRows"
                :total-estimated="generation.totalEstimated"
                :total-actual="generation.totalActual"
                :exact-charge="generation.exactCharge"
                :released-hold="generation.releasedHold"
            />

            <GenerationTimeline :events="generation.timeline" />
        </div>
    </aside>
</template>
