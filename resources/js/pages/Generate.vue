<script setup lang="ts">
import { Head, useForm, useHttp, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import storeGeneration from '@/actions/App/Http/Controllers/GenerationController';
import estimateGeneration from '@/actions/App/Http/Controllers/GenerationEstimateController';
import CostEstimatePanel from '@/components/generate/CostEstimatePanel.vue';
import MobileGenerateBar from '@/components/generate/MobileGenerateBar.vue';
import PromptEditor from '@/components/generate/PromptEditor.vue';
import QualitySelector from '@/components/generate/QualitySelector.vue';
import ReferenceImages from '@/components/generate/ReferenceImages.vue';
import SizePicker from '@/components/generate/SizePicker.vue';
import { focusFirstInvalidField } from '@/lib/focusFirstInvalidField';
import type {
    GenerateForm,
    GenerationHistoryItem,
    GenerationEstimate,
    QualityOption,
    SizeOption,
} from '@/types';
import type { FlashToast } from '@/types/ui';

const sizes: SizeOption[] = [
    {
        value: '1024x1024',
        ratio: '1:1',
        dimensions: '1024 × 1024',
        description: 'Square',
        previewClass: 'size-14',
        isDefault: true,
    },
    {
        value: '1536x1024',
        ratio: '3:2',
        dimensions: '1536 × 1024',
        description: 'Landscape',
        previewClass: 'h-14 w-20',
    },
    {
        value: '1024x1536',
        ratio: '2:3',
        dimensions: '1024 × 1536',
        description: 'Portrait',
        previewClass: 'h-18 w-12',
    },
];

const props = defineProps<{
    /** The generation being remixed, or null for a fresh form. */
    remix: GenerationHistoryItem | null;
}>();

const qualities: QualityOption[] = [
    { value: 'low', label: 'Low', description: 'Fastest' },
    { value: 'medium', label: 'Medium', description: 'Default' },
    { value: 'high', label: 'High', description: 'Most detail' },
];

/**
 * The generation form, submitted as an Inertia visit (redirects, errors, flash toasts).
 * Precognition validates fields live against the server's StoreGenerationRequest rules.
 */
const form = useForm<GenerateForm>({
    prompt: '',
    size: '1024x1024',
    quality: 'medium',
    reference_images: [],
})
    .withPrecognition(storeGeneration())
    .setValidationTimeout(500);

/**
 * Remixing (/?remix={id}) starts from that generation's prompt and settings.
 * They are filled in after the form is created, so form.reset() still empties it after a submit.
 */
if (props.remix) {
    form.prompt = props.remix.prompt;
    form.size = props.remix.size;
    form.quality = props.remix.quality;
    form.reference_images = [...props.remix.reference_images];
}

const page = usePage();

/** Generate stays disabled until there is a prompt; the server validates it either way. */
const isPromptEmpty = computed(() => form.prompt.trim() === '');
const isAuthenticated = computed(() => page.props.auth.user !== null);

/** Guests can't submit, so their fields aren't validated live either. */
function validatePrompt(): void {
    if (isAuthenticated.value) {
        form.validate('prompt');
    }
}

function validateReferenceImages(): void {
    if (isAuthenticated.value) {
        form.validate({ only: ['reference_images', 'reference_images.*'] });
    }
}

function submit(): void {
    form.submit({
        preserveScroll: true,
        onSuccess: (page) => {
            const toast = page.flash.toast as FlashToast | undefined;

            if (toast?.type === 'success') {
                form.reset();
            }
        },
        // On a phone the failing field is often scrolled away behind the bottom bar
        onError: (errors) => nextTick(() => focusFirstInvalidField(errors)),
    });
}

/** The live estimate, fetched as plain JSON (no page visit) whenever the form changes. */
const estimateRequest = useHttp<
    {
        prompt: string;
        size: GenerateForm['size'];
        quality: GenerateForm['quality'];
        reference_image_count: number;
    },
    GenerationEstimate
>({
    prompt: '',
    size: '1024x1024',
    quality: 'medium',
    reference_image_count: 0,
});

const estimate = ref<GenerationEstimate | null>(null);

/**
 * Submitting holds the maximum estimate, so a balance below it can't generate.
 * Shown up front; the server's hold check still has the final word.
 */
const hasInsufficientBalance = computed(
    () =>
        estimate.value !== null &&
        page.props.balanceNano !== null &&
        page.props.balanceNano < estimate.value.total_max_nano,
);
const isEstimateUnavailable = ref(false);

function refreshEstimate(): void {
    estimateRequest.cancel();

    estimateRequest.prompt = form.prompt;
    estimateRequest.size = form.size;
    estimateRequest.quality = form.quality;
    estimateRequest.reference_image_count = form.reference_images.length;

    estimateRequest
        .submit(estimateGeneration(), {
            onHttpException: () => {
                isEstimateUnavailable.value = true;
            },
            onNetworkError: () => {
                isEstimateUnavailable.value = true;
            },
        })
        .then((response) => {
            if (response) {
                estimate.value = response;
                isEstimateUnavailable.value = false;
            }
        })
        .catch(() => {
            // Cancelled or failed: handled by the callbacks above.
        });
}

const refreshEstimateSoon = useDebounceFn(refreshEstimate, 250);

watch(
    () => [form.prompt, form.size, form.quality, form.reference_images.length],
    refreshEstimateSoon,
);

onMounted(refreshEstimate);
</script>

<template>
    <Head title="Generate" />

    <form
        class="grid grid-cols-1 items-start gap-3 pb-24 lg:grid-cols-12 lg:pb-0"
        @submit.prevent="submit"
    >
        <div class="flex flex-col gap-3 lg:col-span-7 xl:col-span-8">
            <PromptEditor
                v-model="form.prompt"
                :error="form.errors.prompt"
                @change="validatePrompt"
            />
            <SizePicker v-model="form.size" :options="sizes" />
            <QualitySelector v-model="form.quality" :options="qualities" />
            <ReferenceImages
                v-model="form.reference_images"
                :max-images="4"
                :errors="form.errors"
                @change="validateReferenceImages"
            />
        </div>

        <div class="lg:sticky lg:top-16 lg:col-span-5 xl:col-span-4">
            <CostEstimatePanel
                :estimate="estimate"
                :is-estimating="estimateRequest.processing"
                :is-estimate-unavailable="isEstimateUnavailable"
                :is-submitting="form.processing"
                :is-prompt-empty="isPromptEmpty"
                :has-insufficient-balance="hasInsufficientBalance"
            />
        </div>

        <MobileGenerateBar
            :estimate="estimate"
            :is-estimating="estimateRequest.processing"
            :is-estimate-unavailable="isEstimateUnavailable"
            :is-submitting="form.processing"
            :is-prompt-empty="isPromptEmpty"
            :has-insufficient-balance="hasInsufficientBalance"
        />
    </form>
</template>
