<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import type { ReferenceImage } from '@/types';

defineProps<{
    image: ReferenceImage;
}>();
</script>

<template>
    <div
        class="flex items-center gap-2 rounded border border-outline-variant/30 bg-surface-container-low p-1"
    >
        <img
            :src="image.thumbnailUrl"
            :alt="image.role"
            class="size-12 shrink-0 rounded border border-outline-variant/50 bg-surface-container-highest object-cover"
        />

        <div class="min-w-0 flex-1">
            <div class="mb-1 flex items-center justify-between gap-2">
                <span class="truncate font-mono text-[11px] text-on-surface">
                    {{ image.url }}
                </span>
                <span
                    class="shrink-0 font-mono text-mono-badge text-primary tabular-nums"
                >
                    Weight: {{ image.weight.toFixed(2) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-container-highest"
                >
                    <div
                        class="h-full rounded-full bg-primary"
                        :style="{ width: `${image.weight * 100}%` }"
                    />
                </div>
                <span class="font-mono text-[10px] text-outline">
                    {{ image.role }}
                </span>
            </div>
        </div>

        <button
            type="button"
            title="Delete"
            class="flex size-7 items-center justify-center rounded text-outline transition-colors hover:bg-error/10 hover:text-error"
        >
            <Trash2 class="size-4" />
        </button>
    </div>
</template>
