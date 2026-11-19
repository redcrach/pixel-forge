<script setup lang="ts">
import SectionLabel from '@/components/SectionLabel.vue';
import type { ImageQuality, QualityOption } from '@/types';

defineProps<{
    options: QualityOption[];
}>();

const quality = defineModel<ImageQuality>({ required: true });
</script>

<template>
    <section
        data-field="quality"
        class="flex flex-col gap-2 border-2 border-border-subtle bg-surface-raised p-2 shadow-pixel"
    >
        <div class="bg-surface-container-high px-2 py-1">
            <SectionLabel>Quality</SectionLabel>
        </div>

        <div
            class="grid grid-cols-3 gap-1 bg-surface-container-lowest p-1 shadow-pixel-inset"
            role="radiogroup"
            aria-label="Quality"
        >
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                role="radio"
                :aria-checked="quality === option.value"
                class="min-h-11 px-2 py-1 text-center text-label-dense uppercase transition-colors sm:min-h-0"
                :class="
                    quality === option.value
                        ? 'bg-primary font-bold text-primary-foreground shadow-pixel-inset'
                        : 'bg-surface-container-low text-on-surface-variant shadow-pixel-sm hover:bg-surface-container-high hover:text-on-surface'
                "
                @click="quality = option.value"
            >
                <span class="font-bold">[ {{ option.label }} ]</span>
                <span
                    class="block font-mono text-[10px] max-[359px]:hidden"
                    :class="
                        quality === option.value
                            ? 'text-primary-foreground/80'
                            : 'text-outline'
                    "
                >
                    {{ option.description }}
                </span>
            </button>
        </div>
    </section>
</template>
