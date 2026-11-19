<script setup lang="ts">
import SectionLabel from '@/components/SectionLabel.vue';
import type { ImageSize, SizeOption } from '@/types';

defineProps<{
    options: SizeOption[];
}>();

const size = defineModel<ImageSize>({ required: true });
</script>

<template>
    <section
        class="flex flex-col gap-2 rounded-lg border border-border-subtle bg-surface-raised p-3"
    >
        <SectionLabel>Size</SectionLabel>

        <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Size">
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                role="radio"
                :aria-checked="size === option.value"
                class="relative flex flex-col rounded-lg p-2 text-left transition-all"
                :class="
                    size === option.value
                        ? 'border-2 border-primary bg-surface-container-low shadow-[0_0_12px_rgba(154,230,0,0.25)]'
                        : 'border border-border-subtle bg-surface-container-lowest hover:border-border-prominent hover:bg-surface-container-low'
                "
                @click="size = option.value"
            >
                <span
                    v-if="option.isDefault"
                    class="absolute top-2 right-2 hidden rounded-full bg-primary px-1.5 py-0.5 font-mono text-mono-badge font-semibold text-primary-foreground uppercase sm:inline"
                >
                    Default
                </span>

                <span class="my-0.5 flex h-20 items-center justify-center">
                    <span
                        class="flex items-center justify-center rounded-sm"
                        :class="[
                            option.previewClass,
                            size === option.value
                                ? 'border-2 border-primary bg-primary/10'
                                : 'border border-border-prominent bg-surface-container',
                        ]"
                    >
                        <span
                            class="font-mono text-[10px]"
                            :class="
                                size === option.value
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
                    <span class="font-mono text-label-dense text-outline">
                        {{ option.description }}
                    </span>
                </span>
            </button>
        </div>
    </section>
</template>
