<script setup lang="ts">
import { Link2, Plus, Trash2 } from '@lucide/vue';
import SectionLabel from '@/components/SectionLabel.vue';

defineProps<{
    urls: string[];
    maxImages: number;
}>();
</script>

<template>
    <section
        class="flex flex-col gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-lowest p-2 shadow-sm"
    >
        <div class="flex items-center justify-between gap-2">
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center gap-1">
                    <SectionLabel>Reference images</SectionLabel>
                    <span
                        class="rounded bg-surface-container-high px-1.5 font-mono text-mono-badge text-on-surface-variant"
                    >
                        {{ urls.length }} / {{ maxImages }}
                    </span>
                </div>
                <p class="text-label-dense text-on-surface-variant">
                    Optional. Image URLs only, each billed as 765 image tokens.
                </p>
            </div>
        </div>

        <div v-if="urls.length" class="flex flex-col gap-1">
            <div
                v-for="(url, index) in urls"
                :key="index"
                class="flex items-center gap-2 rounded border border-outline-variant/30 bg-surface-container-low p-1"
            >
                <img
                    :src="url"
                    alt=""
                    class="size-10 shrink-0 rounded border border-outline-variant/50 bg-surface-container-highest object-cover"
                />
                <input
                    type="url"
                    :value="url"
                    :aria-label="`Reference image ${index + 1}`"
                    class="min-w-0 flex-1 bg-transparent font-mono text-[11px] text-on-surface focus:outline-none"
                />
                <button
                    type="button"
                    title="Remove"
                    class="flex size-7 shrink-0 items-center justify-center rounded text-outline transition-colors hover:bg-error/10 hover:text-error"
                >
                    <Trash2 class="size-4" />
                </button>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <div class="relative flex-1">
                <Link2
                    class="absolute top-1/2 left-2 size-4 -translate-y-1/2 text-outline"
                />
                <input
                    type="url"
                    placeholder="https://example.com/reference.png"
                    aria-label="Reference image URL"
                    class="w-full rounded border border-outline-variant/30 bg-surface-container-low py-1 pr-2 pl-8 font-mono text-[11px] text-on-surface placeholder:text-outline focus:ring-1 focus:ring-primary focus:outline-none"
                />
            </div>
            <button
                type="button"
                class="flex shrink-0 items-center gap-0.5 rounded border border-outline-variant/30 bg-surface-container px-2 py-1 text-label-dense text-on-surface transition-colors hover:bg-surface-container-high"
            >
                <Plus class="size-3.5" />
                Add
            </button>
        </div>
    </section>
</template>
