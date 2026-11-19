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
        class="flex flex-col overflow-hidden rounded-lg border bg-surface-raised transition-colors"
        :class="
            error
                ? 'border-error/60'
                : 'border-border-subtle focus-within:border-primary'
        "
    >
        <div
            class="flex items-center justify-between border-b border-border-subtle px-3 py-2"
        >
            <label for="prompt">
                <SectionLabel>Prompt</SectionLabel>
            </label>
            <span
                class="rounded-full border border-border-subtle bg-surface-container-lowest px-1.5 py-0.5 font-mono text-mono-badge tabular-nums"
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

        <div class="p-3">
            <textarea
                id="prompt"
                v-model="prompt"
                rows="5"
                class="w-full resize-none bg-transparent font-mono text-base leading-relaxed text-on-surface placeholder:text-outline/50 focus:outline-none sm:text-sm"
                placeholder="Describe the image you want to generate..."
                @change="emit('change')"
            />
            <InputError :message="error" />
        </div>
    </section>
</template>
