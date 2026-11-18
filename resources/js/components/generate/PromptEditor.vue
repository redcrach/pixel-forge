<script setup lang="ts">
import { CircleMinus, Dices, WandSparkles } from '@lucide/vue';
import SectionLabel from '@/components/SectionLabel.vue';

const actions = [
    {
        label: '+ Enhance with GPT-4o',
        icon: WandSparkles,
        iconClass: 'text-tertiary',
    },
    {
        label: '+ Add negative prompt',
        icon: CircleMinus,
        iconClass: 'text-outline',
    },
    { label: '+ Random seed', icon: Dices, iconClass: 'text-outline' },
];
</script>

<template>
    <section
        class="flex flex-col overflow-hidden rounded-lg border border-outline-variant/40 bg-surface-container-lowest shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-outline-variant/30 bg-surface-container-low px-2 py-1"
        >
            <div class="flex items-center gap-2">
                <SectionLabel>Prompt</SectionLabel>
                <label
                    class="flex items-center rounded border border-outline-variant/40 bg-surface-container-lowest px-1.5 py-0.5"
                >
                    <span
                        class="mr-1.5 text-label-dense text-on-surface-variant"
                    >
                        Preset:
                    </span>
                    <select
                        class="cursor-pointer bg-transparent text-label-dense font-medium text-primary focus:outline-none"
                    >
                        <option value="photo" selected>Photorealistic</option>
                        <option value="arch">Architectural</option>
                        <option value="concept">Concept Art</option>
                        <option value="iso">Isometric 3D</option>
                    </select>
                </label>
            </div>
            <span
                class="rounded bg-surface-container px-1 py-0.5 font-mono text-mono-badge text-outline"
            >
                214 / 4000
            </span>
        </div>

        <div class="p-2">
            <textarea
                rows="4"
                class="w-full resize-none bg-transparent font-mono text-body-default leading-relaxed text-on-surface placeholder:text-outline/50 focus:outline-none"
                placeholder="Describe the scene, camera composition, atmospheric rendering..."
            >
Cinematic wide angle shot of an obsidian brutalist monolithic laboratory floating in misty arctic waters, moody volumetric lighting, sharp rim illumination, anamorphic lens flare, 8k resolution, photorealistic render</textarea>

            <div
                class="mt-2 flex flex-wrap items-center justify-between gap-1 border-t border-outline-variant/20 pt-1"
            >
                <div class="flex flex-wrap items-center gap-0.5">
                    <button
                        v-for="action in actions"
                        :key="action.label"
                        type="button"
                        class="flex items-center gap-0.5 rounded border border-outline-variant/30 bg-surface-container-high/60 px-1 py-1 text-label-dense text-on-surface transition-colors hover:bg-surface-container-highest"
                    >
                        <component
                            :is="action.icon"
                            class="size-3.5"
                            :class="action.iconClass"
                        />
                        {{ action.label }}
                    </button>
                </div>
                <span class="font-mono text-mono-badge text-outline">
                    Token est: 58
                </span>
            </div>
        </div>
    </section>
</template>
