<script setup lang="ts">
import { ImageOff, Link2, Plus, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import SectionLabel from '@/components/SectionLabel.vue';

const props = defineProps<{
    maxImages: number;
    /** The form's errors, read as `reference_images` and `reference_images.N`. */
    errors: Partial<Record<string, string>>;
}>();

const emit = defineEmits<{
    /** A URL was added or removed. */
    change: [];
}>();

const urls = defineModel<string[]>({ required: true });

const newUrl = ref('');
const newUrlError = ref<string | null>(null);
const brokenPreviews = ref<Set<string>>(new Set());

const canAddMore = computed(() => urls.value.length < props.maxImages);

watch(newUrl, () => {
    newUrlError.value = null;
});

/** Same check as the server's `url:http,https` rule, so an invalid URL never enters the list. */
function isHttpUrl(value: string): boolean {
    if (!URL.canParse(value)) {
        return false;
    }

    const { protocol } = new URL(value);

    return protocol === 'http:' || protocol === 'https:';
}

function addUrl(): void {
    const url = newUrl.value.trim();

    if (url === '' || !canAddMore.value) {
        return;
    }

    if (!isHttpUrl(url)) {
        newUrlError.value = 'Reference images must be http(s) URLs.';

        return;
    }

    urls.value = [...urls.value, url];
    newUrl.value = '';
    emit('change');
}

function removeUrl(index: number): void {
    urls.value = urls.value.filter((_, position) => position !== index);
    emit('change');
}

function markPreviewBroken(url: string): void {
    brokenPreviews.value = new Set(brokenPreviews.value).add(url);
}
</script>

<template>
    <section
        data-field="reference_images"
        class="flex flex-col gap-2 border-2 border-border-subtle bg-surface-raised p-2 shadow-pixel"
    >
        <div class="flex flex-col gap-1">
            <div
                class="flex items-center justify-between gap-1 bg-surface-container-high px-2 py-1"
            >
                <SectionLabel>Reference images</SectionLabel>
                <span
                    class="bg-surface-container-lowest px-1 font-mono text-mono-badge text-wheat"
                >
                    [ {{ urls.length }} / {{ maxImages }} ]
                </span>
            </div>
            <p class="px-1 text-label-dense text-on-surface-variant">
                Optional. Image URLs only, each billed as 765 image tokens.
            </p>
        </div>

        <ul v-if="urls.length" class="flex flex-col gap-1">
            <li
                v-for="(url, index) in urls"
                :key="`${index}-${url}`"
                :data-field="`reference_images.${index}`"
                class="flex flex-col gap-0.5"
            >
                <div
                    class="flex items-center gap-2 border-2 bg-surface-container-lowest p-1.5 shadow-pixel-sm"
                    :class="
                        errors[`reference_images.${index}`]
                            ? 'border-error'
                            : 'border-transparent'
                    "
                >
                    <span
                        class="flex size-10 shrink-0 items-center justify-center overflow-hidden bg-surface-container-highest text-outline [image-rendering:pixelated]"
                    >
                        <ImageOff
                            v-if="brokenPreviews.has(url)"
                            class="size-4"
                        />
                        <img
                            v-else
                            :src="url"
                            alt=""
                            class="size-full object-cover"
                            @error="markPreviewBroken(url)"
                        />
                    </span>
                    <span
                        class="min-w-0 flex-1 truncate font-mono text-[11px] text-on-surface"
                    >
                        {{ url }}
                    </span>
                    <button
                        type="button"
                        title="Remove"
                        class="flex size-11 shrink-0 items-center justify-center bg-error-container text-error transition-colors hover:bg-error hover:text-error-container sm:size-7"
                        @click="removeUrl(index)"
                    >
                        <Trash2 class="size-4" />
                    </button>
                </div>
                <InputError :message="errors[`reference_images.${index}`]" />
            </li>
        </ul>

        <div v-if="canAddMore" class="flex flex-col gap-0.5">
            <div class="flex items-center gap-1">
                <div class="relative flex-1">
                    <Link2
                        class="absolute top-1/2 left-2 size-4 -translate-y-1/2 text-outline"
                    />
                    <input
                        v-model="newUrl"
                        type="url"
                        placeholder="https://example.com/reference.png"
                        aria-label="Reference image URL"
                        :aria-invalid="newUrlError !== null"
                        class="min-h-11 w-full border-2 bg-surface-container-lowest py-1.5 pr-2 pl-8 font-mono text-base text-primary shadow-pixel-inset placeholder:text-outline focus:border-primary focus:outline-none sm:min-h-0 sm:text-sm"
                        :class="
                            newUrlError ? 'border-error' : 'border-transparent'
                        "
                        @keydown.enter.prevent="addUrl"
                    />
                </div>
                <button
                    type="button"
                    :disabled="newUrl.trim() === ''"
                    class="pixel-press flex min-h-11 shrink-0 items-center gap-0.5 bg-surface-container-high px-3 py-1.5 text-label-dense font-bold text-on-surface uppercase transition-colors hover:bg-surface-container-highest disabled:opacity-50 sm:min-h-0 sm:px-2"
                    @click="addUrl"
                >
                    <Plus class="size-3.5 text-wheat" />
                    [ Add ]
                </button>
            </div>
            <InputError :message="newUrlError ?? undefined" />
        </div>

        <InputError :message="errors.reference_images" />
    </section>
</template>
