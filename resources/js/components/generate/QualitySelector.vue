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
        class="flex flex-col gap-2 rounded-lg border border-border-subtle bg-surface-raised p-3"
    >
        <SectionLabel>Quality</SectionLabel>

        <div
            class="grid grid-cols-3 gap-1 rounded-lg border border-border-subtle bg-surface-container-lowest p-1"
            role="radiogroup"
            aria-label="Quality"
        >
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                role="radio"
                :aria-checked="quality === option.value"
                class="rounded px-2 py-1 text-center text-label-dense transition-colors"
                :class="
                    quality === option.value
                        ? 'bg-primary font-semibold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)]'
                        : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'
                "
                @click="quality = option.value"
            >
                <span class="font-medium">{{ option.label }}</span>
                <span
                    class="block font-mono text-[10px]"
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
