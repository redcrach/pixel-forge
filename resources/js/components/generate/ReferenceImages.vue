<script setup lang="ts">
import { CloudUpload, ImagePlus, Plus } from '@lucide/vue';
import { computed } from 'vue';
import ReferenceImageRow from '@/components/generate/ReferenceImageRow.vue';
import SectionLabel from '@/components/SectionLabel.vue';
import type { ReferenceImage } from '@/types';

const props = defineProps<{
    images: ReferenceImage[];
    maxImages: number;
}>();

const emptySlotNumbers = computed(() =>
    Array.from(
        { length: Math.max(props.maxImages - props.images.length, 0) },
        (_, index) => props.images.length + index + 1,
    ),
);
</script>

<template>
    <section
        class="flex flex-col gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-lowest p-2 shadow-sm"
    >
        <div class="flex items-center justify-between gap-2">
            <div>
                <div class="flex items-center gap-1">
                    <SectionLabel>Reference Images</SectionLabel>
                    <span
                        class="rounded bg-surface-container-high px-1.5 font-mono text-mono-badge text-on-surface-variant"
                    >
                        {{ images.length }} of {{ maxImages }} used
                    </span>
                </div>
                <p class="mt-0.5 text-label-dense text-on-surface-variant">
                    Upload or input image URLs, max {{ maxImages }} images for
                    composition or style transfer
                </p>
            </div>
            <button
                type="button"
                class="flex shrink-0 items-center gap-0.5 rounded border border-outline-variant/30 bg-surface-container px-2 py-1 text-label-dense text-on-surface transition-colors hover:bg-surface-container-high"
            >
                <Plus class="size-3.5" />
                Add Slot
            </button>
        </div>

        <div class="flex flex-col gap-1">
            <ReferenceImageRow
                v-for="image in images"
                :key="image.url"
                :image="image"
            />

            <button
                v-for="slotNumber in emptySlotNumbers"
                :key="slotNumber"
                type="button"
                class="group flex items-center justify-between rounded border border-dashed border-outline-variant/40 bg-surface-container-lowest p-1 text-left transition-colors hover:border-outline-variant"
            >
                <span class="flex items-center gap-2">
                    <span
                        class="flex size-12 items-center justify-center rounded border border-dashed border-outline-variant/60 text-outline transition-colors group-hover:text-primary"
                    >
                        <ImagePlus class="size-4.5" />
                    </span>
                    <span class="flex flex-col">
                        <span
                            class="text-body-dense text-on-surface-variant transition-colors group-hover:text-on-surface"
                        >
                            Drop style image or click to browse
                        </span>
                        <span class="text-label-dense text-outline">
                            Empty slot #{{ slotNumber }} • PNG, JPG or WEBP up
                            to 20MB
                        </span>
                    </span>
                </span>
                <span
                    class="rounded bg-surface-container-low px-1 py-1 text-label-dense text-outline transition-colors group-hover:bg-surface-container"
                >
                    Assign Ref
                </span>
            </button>
        </div>

        <button
            type="button"
            class="flex w-full items-center justify-center gap-1 rounded border border-outline-variant/30 bg-surface-container-low py-1 text-body-dense text-on-surface transition-colors hover:border-outline-variant hover:bg-surface-container"
        >
            <CloudUpload class="size-4 text-primary" />
            + Add Reference Image ({{ images.length }} of {{ maxImages }} used)
        </button>
    </section>
</template>
