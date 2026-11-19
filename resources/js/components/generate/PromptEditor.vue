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
        class="flex flex-col overflow-hidden rounded-lg border bg-surface-container-lowest shadow-sm"
        :class="error ? 'border-error/60' : 'border-outline-variant/40'"
    >
        <div
            class="flex items-center justify-between border-b border-outline-variant/30 bg-surface-container-low px-2 py-1"
        >
            <label for="prompt">
                <SectionLabel>Prompt</SectionLabel>
            </label>
            <span
                class="rounded bg-surface-container px-1 py-0.5 font-mono text-mono-badge tabular-nums"
                :class="
                    characterCount > maxCharacters
                        ? 'text-error'
                        : 'text-outline'
                "
            >
                {{ characterCount.toLocaleString() }} /
                {{ maxCharacters.toLocaleString() }}
            </span>
        </div>

        <div class="p-2">
            <textarea
                id="prompt"
                v-model="prompt"
                rows="5"
                class="w-full resize-none bg-transparent font-mono text-body-default leading-relaxed text-on-surface placeholder:text-outline/50 focus:outline-none"
                placeholder="Describe the image you want to generate..."
                @change="emit('change')"
            />
            <InputError :message="error" />
        </div>
    </section>
</template>
