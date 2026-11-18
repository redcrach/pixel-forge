<script setup lang="ts">
import SectionLabel from '@/components/SectionLabel.vue';
import type { AspectRatioOption } from '@/types';

defineProps<{
    options: AspectRatioOption[];
}>();
</script>

<template>
    <section
        class="flex flex-col gap-2 rounded-lg border border-outline-variant/40 bg-surface-container-lowest p-2 shadow-sm"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-1">
                <SectionLabel>Aspect Ratio &amp; Canvas</SectionLabel>
                <span
                    class="rounded border border-tertiary/20 bg-tertiary/10 px-1.5 py-0.5 font-mono text-mono-badge text-tertiary"
                >
                    HD Matrix
                </span>
            </div>
            <span class="text-label-dense text-on-surface-variant">
                Selected: 1:1 Square
            </span>
        </div>

        <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
            <button
                v-for="option in options"
                :key="option.ratio"
                type="button"
                class="relative flex flex-col rounded-lg p-2 text-left transition-all"
                :class="
                    option.isSelected
                        ? 'border-2 border-primary/90 bg-surface-container-low shadow-sm hover:bg-surface-container'
                        : 'border border-outline-variant/40 bg-surface-container-lowest hover:border-outline hover:bg-surface-container-low'
                "
            >
                <span
                    v-if="option.isDefault"
                    class="absolute top-2 right-2 rounded bg-primary px-1.5 py-0.5 font-mono text-mono-badge font-semibold text-primary-foreground"
                >
                    Default
                </span>

                <span class="my-0.5 flex h-20 items-center justify-center">
                    <span
                        class="flex items-center justify-center rounded-sm"
                        :class="[
                            option.previewClass,
                            option.isSelected
                                ? 'border-2 border-primary bg-primary/10'
                                : 'border border-outline-variant/60 bg-surface-container',
                        ]"
                    >
                        <span
                            class="font-mono text-[10px]"
                            :class="
                                option.isSelected
                                    ? 'font-bold text-primary'
                                    : 'text-on-surface-variant'
                            "
                        >
                            {{ option.ratio }}
                        </span>
                    </span>
                </span>

                <span class="mt-auto flex flex-col">
                    <span
                        class="text-label-default font-semibold text-on-surface"
                    >
                        {{ option.dimensions }}
                    </span>
                    <span class="text-label-dense text-on-surface-variant">
                        {{ option.description }}
                    </span>
                </span>
            </button>
        </div>
    </section>
</template>
