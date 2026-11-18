<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AspectRatioPicker from '@/components/generate/AspectRatioPicker.vue';
import CostEstimatePanel from '@/components/generate/CostEstimatePanel.vue';
import InferencePipelineCard from '@/components/generate/InferencePipelineCard.vue';
import LowBalanceBanner from '@/components/generate/LowBalanceBanner.vue';
import PromptEditor from '@/components/generate/PromptEditor.vue';
import QualitySelector from '@/components/generate/QualitySelector.vue';
import ReferenceImages from '@/components/generate/ReferenceImages.vue';
import StudioToast from '@/components/StudioToast.vue';
import type {
    AspectRatioOption,
    CostLineItem,
    QualityOption,
    ReferenceImage,
} from '@/types';

const aspectRatios: AspectRatioOption[] = [
    {
        ratio: '1:1',
        dimensions: '1024 × 1024',
        description: 'Square • Universal',
        previewClass: 'size-14',
        isDefault: true,
        isSelected: true,
    },
    {
        ratio: '3:2',
        dimensions: '1536 × 1024',
        description: 'Landscape • Cinematic',
        previewClass: 'h-14 w-20',
    },
    {
        ratio: '2:3',
        dimensions: '1024 × 1536',
        description: 'Portrait • Mobile Spec',
        previewClass: 'h-18 w-12',
    },
];

const qualities: QualityOption[] = [
    { label: 'Low', description: 'Draft • Fast (12s)' },
    { label: 'Medium', description: 'Standard (24s)' },
    {
        label: 'High (Ultra Precision)',
        description: '4-Pass Guided (48s)',
        isSelected: true,
    },
];

const referenceImages: ReferenceImage[] = [
    {
        url: 'https://assets.pixelforge.io/refs/arch-nordic-01.jpg',
        thumbnailUrl:
            'https://lh3.googleusercontent.com/aida-public/AB6AXuBLSmzBKLgoidrUbwW5TlFTpemASB7AnxN35SsjcFjtaEBHT8g7uavEApCPVYH5YUdXfZUcID8UmhbluxWUX_aOjxvCTaw1lhqgmqeTXX3J8UEq2Vc8LLeZ6eOgq0ziw9Y1IBtEvKMrz6SK7n1DjRcy1l1142mNUB7AaXsvuCb5HMA2DYiB2a6wXQyePs1TW8K2CuRenROdXTY26A43t2UzQ_i8I9PMRLlQ_ALNm5zg',
        weight: 0.75,
        role: 'Structure',
    },
    {
        url: 'https://assets.pixelforge.io/refs/mist-lighting-4k.png',
        thumbnailUrl:
            'https://lh3.googleusercontent.com/aida-public/AB6AXuD_haev7w31lidpXKfEn8LEIOtWubdLepTU70Sfzo1kCoG9sY6EbRH26pv61gi5k7UrM2avOlDL9KTSFnelihnzd2ng37PV18EssoONSVyD_4xYQHp3WZ6ihfq5hH-lXJgdv5ODv1mf7FcjZt29mBvWxKz9zolrCVhzwcISh6Xi3KFRm99nXXFpviiwFLBZXO0V10oyA6nfEgQrBCm3R288q7MDlH4ZUfWf_wlGZEpt',
        weight: 0.5,
        role: 'Lighting',
    },
];

const costLineItems: CostLineItem[] = [
    {
        label: 'Input text (Prompt)',
        detail: '214 chars / 58 tokens',
        amount: '$0.0014',
        isEstimate: true,
    },
    {
        label: 'Reference images (2x)',
        detail: '2 images (1024px)',
        amount: '$0.0240',
    },
    {
        label: 'Output image (1024×1024 High)',
        detail: 'Base render tile',
        amount: '$0.0400',
    },
    {
        label: 'Reasoning & Multi-pass',
        detail: '4 reasoning steps',
        amount: '$0.0026 – $0.0306',
        isEstimate: true,
    },
];

const isBannerVisible = ref(true);
const isThrottleToastVisible = ref(true);
const isQuotaToastVisible = ref(true);
</script>

<template>
    <Head title="Generate" />

    <div class="flex flex-col gap-3">
        <LowBalanceBanner
            v-if="isBannerVisible"
            balance="$4.2731"
            @dismiss="isBannerVisible = false"
        />

        <div class="grid grid-cols-1 items-start gap-3 xl:grid-cols-12">
            <div class="flex flex-col gap-3 xl:col-span-8">
                <PromptEditor />
                <AspectRatioPicker :options="aspectRatios" />
                <QualitySelector :options="qualities" />
                <ReferenceImages :images="referenceImages" :max-images="4" />
            </div>

            <div class="flex flex-col gap-2 xl:sticky xl:top-16 xl:col-span-4">
                <CostEstimatePanel :line-items="costLineItems" />
                <InferencePipelineCard />
            </div>
        </div>

        <div
            class="pointer-events-none fixed inset-x-4 bottom-6 z-50 flex flex-col gap-1 sm:inset-x-auto sm:right-6 sm:w-full sm:max-w-sm"
        >
            <StudioToast
                v-if="isThrottleToastVisible"
                title="Capacity Throttle"
                badge="1.0s"
                tone="warning"
                @dismiss="isThrottleToastVisible = false"
            >
                Pixelforge is overloaded — retrying in
                <span class="font-mono font-semibold text-on-surface">
                    1.0s
                </span>
            </StudioToast>

            <StudioToast
                v-if="isQuotaToastVisible"
                title="Quota Exhausted"
                badge="Daily Cap"
                tone="info"
                @dismiss="isQuotaToastVisible = false"
            >
                Daily generation limit reached — try again tomorrow (UTC)
                <div class="mt-0.5 flex items-center gap-1 text-label-dense">
                    <span class="font-medium text-primary">
                        Upgrade to Tier 3
                    </span>
                    <span class="text-outline">•</span>
                    <span class="font-mono text-outline">
                        Resets in 05h 14m
                    </span>
                </div>
            </StudioToast>
        </div>
    </div>
</template>
