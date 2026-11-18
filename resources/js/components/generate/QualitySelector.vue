<script setup lang="ts">
import SectionLabel from '@/components/SectionLabel.vue';
import type { QualityOption } from '@/types';

defineProps<{
    options: QualityOption[];
}>();
</script>

<template>
    <section
        class="flex flex-col gap-1 rounded-lg border border-outline-variant/40 bg-surface-container-lowest p-2 shadow-sm"
    >
        <div class="flex items-center justify-between">
            <SectionLabel>Sampling Quality Engine</SectionLabel>
            <span class="text-label-dense text-on-surface-variant">
                Diffusion Iterations: 50 steps
            </span>
        </div>

        <div
            class="grid grid-cols-3 gap-1 rounded-lg border border-outline-variant/50 bg-surface-container-lowest p-1"
        >
            <button
                v-for="option in options"
                :key="option.label"
                type="button"
                class="relative overflow-hidden rounded px-2 py-1 text-center text-label-dense transition-colors"
                :class="
                    option.isSelected
                        ? 'bg-primary font-semibold text-primary-foreground shadow'
                        : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'
                "
            >
                <span
                    v-if="option.isSelected"
                    class="pointer-events-none absolute inset-0 bg-linear-to-r from-transparent via-white/10 to-transparent"
                />
                <span class="font-medium">{{ option.label }}</span>
                <span
                    class="block font-mono text-[10px]"
                    :class="
                        option.isSelected
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
