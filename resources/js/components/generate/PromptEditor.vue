<script setup lang="ts">
import SectionLabel from '@/components/SectionLabel.vue';
</script>

<template>
    <section
        class="flex flex-col overflow-hidden rounded-lg border border-outline-variant/40 bg-surface-container-lowest shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-outline-variant/30 bg-surface-container-low px-2 py-1"
        >
            <label for="prompt">
                <SectionLabel>Prompt</SectionLabel>
            </label>
            <span
                class="rounded bg-surface-container px-1 py-0.5 font-mono text-mono-badge text-outline tabular-nums"
            >
                214 / 4,000
            </span>
        </div>

        <div class="p-2">
            <textarea
                id="prompt"
                rows="5"
                maxlength="4000"
                class="w-full resize-none bg-transparent font-mono text-body-default leading-relaxed text-on-surface placeholder:text-outline/50 focus:outline-none"
                placeholder="Describe the image you want to generate..."
            >
Cinematic wide angle shot of an obsidian brutalist monolithic laboratory floating in misty arctic waters, moody volumetric lighting, sharp rim illumination, anamorphic lens flare, 8k resolution, photorealistic render</textarea>

            <div
                class="mt-2 flex justify-end border-t border-outline-variant/20 pt-1"
            >
                <span class="font-mono text-mono-badge text-outline">
                    ≈ 54 text tokens
                </span>
            </div>
        </div>
    </section>
</template>
