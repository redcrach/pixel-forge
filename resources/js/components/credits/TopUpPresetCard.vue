<script setup lang="ts">
import type { TopUpPreset } from '@/types';

defineProps<{
    preset: TopUpPreset;
}>();
</script>

<template>
    <button
        type="button"
        class="relative flex h-28 flex-col justify-between rounded-lg p-2 text-left transition-all"
        :class="
            preset.isSelected
                ? 'bg-surface-container-high shadow-lg ring-1 shadow-primary/5 ring-primary'
                : 'bg-surface-container hover:bg-surface-container-high'
        "
    >
        <span
            v-if="preset.badge"
            class="absolute -top-2.5 right-2 rounded-full px-0.5 py-0.5 font-mono text-mono-badge font-semibold tracking-wide"
            :class="
                preset.tone === 'tertiary'
                    ? 'bg-tertiary-container text-on-tertiary-container'
                    : 'bg-primary text-primary-foreground'
            "
        >
            {{ preset.badge }}
        </span>

        <span class="flex flex-col">
            <span
                class="font-mono text-base font-semibold"
                :class="preset.isSelected ? 'text-primary' : 'text-on-surface'"
            >
                {{ preset.amount }}
            </span>
            <span
                class="mt-1 text-label-dense"
                :class="
                    preset.isSelected
                        ? 'text-on-surface'
                        : 'text-on-surface-variant'
                "
            >
                {{ preset.images }}
            </span>
        </span>

        <span
            class="text-label-dense"
            :class="{
                'text-primary': preset.tone === 'primary',
                'text-tertiary': preset.tone === 'tertiary',
                'text-outline': !preset.tone,
            }"
        >
            {{ preset.caption }}
        </span>
    </button>
</template>
