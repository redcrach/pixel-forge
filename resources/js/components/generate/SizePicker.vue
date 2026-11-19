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
        data-field="size"
        class="flex flex-col gap-2 border-2 border-border-subtle bg-surface-raised p-2 shadow-pixel"
    >
        <div class="bg-surface-container-high px-2 py-1">
            <SectionLabel>Aspect ratio &amp; size</SectionLabel>
        </div>

        <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Size">
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                role="radio"
                :aria-checked="size === option.value"
                class="relative flex flex-col p-2 text-left transition-transform hover:-translate-y-0.5 max-[359px]:p-1.5"
                :class="
                    size === option.value
                        ? 'bg-primary text-primary-foreground shadow-pixel'
                        : 'bg-surface-container-low text-on-surface shadow-pixel-sm hover:bg-surface-container-high'
                "
                @click="size = option.value"
            >
                <span
                    v-if="option.isDefault"
                    class="absolute top-2 right-2 hidden bg-primary-foreground px-1 py-0.5 font-mono text-mono-badge font-bold text-primary uppercase sm:inline"
                >
                    Default
                </span>

                <span
                    class="my-0.5 flex h-16 items-center justify-center sm:h-20"
                >
                    <span
                        class="flex items-center justify-center shadow-pixel-inset"
                        :class="[
                            option.previewClass,
                            size === option.value
                                ? 'bg-primary-foreground'
                                : 'bg-surface-container-lowest',
                        ]"
                    >
                        <span
                            class="font-mono text-[10px]"
                            :class="
                                size === option.value
                                    ? 'font-bold text-primary'
                                    : 'text-wheat'
                            "
                        >
                            {{ option.ratio }}
                        </span>
                    </span>
                </span>

                <span class="mt-auto flex flex-col">
                    <span
                        class="text-label-default font-bold whitespace-nowrap max-[359px]:text-[10px] max-[359px]:tracking-tight"
                    >
                        {{ option.dimensions }}
                    </span>
                    <span
                        class="font-mono text-label-dense uppercase"
                        :class="
                            size === option.value
                                ? 'text-primary-foreground/80'
                                : 'text-outline'
                        "
                    >
                        [{{ option.description }}]
                    </span>
                </span>
            </button>
        </div>
    </section>
</template>
