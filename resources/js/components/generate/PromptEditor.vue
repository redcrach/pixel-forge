<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import SectionLabel from '@/components/SectionLabel.vue';

defineProps<{
    error?: string;
}>();

const emit = defineEmits<{
    /** The prompt was edited and the textarea lost focus. */
    change: [];
}>();

const prompt = defineModel<string>({ required: true });

const maxCharacters = 4000;

/** Unicode characters, like the server's mb_strlen (emoji count as one). */
const characterCount = computed(() => [...prompt.value].length);
</script>

<template>
    <section
        data-field="prompt"
        class="flex flex-col gap-1 overflow-hidden border-2 bg-surface-raised p-1 shadow-pixel transition-colors"
        :class="
            error
                ? 'border-error'
                : 'border-border-subtle focus-within:border-primary'
        "
    >
        <div
            class="flex items-center justify-between bg-surface-container-high px-2 py-1"
        >
            <label for="prompt">
                <SectionLabel>Prompt command</SectionLabel>
            </label>
            <span
                class="bg-surface-container-lowest px-1 py-0.5 font-mono text-mono-badge tabular-nums"
                :class="
                    characterCount > maxCharacters
                        ? 'text-error'
                        : 'text-outline'
                "
            >
                [ {{ characterCount.toLocaleString() }} /
                {{ maxCharacters.toLocaleString() }} ]
            </span>
        </div>

        <div
            class="flex items-start gap-1.5 bg-surface-container-lowest p-3 shadow-pixel-inset"
        >
            <span
                class="font-mono text-base leading-relaxed font-bold text-wheat select-none sm:text-sm"
                aria-hidden="true"
            >
                &gt;
            </span>
            <div class="min-w-0 flex-1">
                <textarea
                    id="prompt"
                    v-model="prompt"
                    rows="5"
                    class="w-full resize-none bg-transparent font-mono text-base leading-relaxed text-primary caret-primary placeholder:text-outline/60 focus:outline-none sm:text-sm"
                    placeholder="Describe the image you want to generate..."
                    @change="emit('change')"
                />
                <InputError :message="error" />
            </div>
        </div>
    </section>
</template>
