<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CostEstimatePanel from '@/components/generate/CostEstimatePanel.vue';
import PromptEditor from '@/components/generate/PromptEditor.vue';
import QualitySelector from '@/components/generate/QualitySelector.vue';
import ReferenceImages from '@/components/generate/ReferenceImages.vue';
import SizePicker from '@/components/generate/SizePicker.vue';
import type { CostLineItem, QualityOption, SizeOption } from '@/types';

const sizes: SizeOption[] = [
    {
        ratio: '1:1',
        dimensions: '1024 × 1024',
        description: 'Square',
        previewClass: 'size-14',
        isDefault: true,
        isSelected: true,
    },
    {
        ratio: '3:2',
        dimensions: '1536 × 1024',
        description: 'Landscape',
        previewClass: 'h-14 w-20',
    },
    {
        ratio: '2:3',
        dimensions: '1024 × 1536',
        description: 'Portrait',
        previewClass: 'h-18 w-12',
    },
];

const qualities: QualityOption[] = [
    { label: 'Low', description: 'Fastest' },
    { label: 'Medium', description: 'Default', isSelected: true },
    { label: 'High', description: 'Most detail' },
];

const referenceUrls: string[] = [
    'https://lh3.googleusercontent.com/aida-public/AB6AXuBLSmzBKLgoidrUbwW5TlFTpemASB7AnxN35SsjcFjtaEBHT8g7uavEApCPVYH5YUdXfZUcID8UmhbluxWUX_aOjxvCTaw1lhqgmqeTXX3J8UEq2Vc8LLeZ6eOgq0ziw9Y1IBtEvKMrz6SK7n1DjRcy1l1142mNUB7AaXsvuCb5HMA2DYiB2a6wXQyePs1TW8K2CuRenROdXTY26A43t2UzQ_i8I9PMRLlQ_ALNm5zg',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuD_haev7w31lidpXKfEn8LEIOtWubdLepTU70Sfzo1kCoG9sY6EbRH26pv61gi5k7UrM2avOlDL9KTSFnelihnzd2ng37PV18EssoONSVyD_4xYQHp3WZ6ihfq5hH-lXJgdv5ODv1mf7FcjZt29mBvWxKz9zolrCVhzwcISh6Xi3KFRm99nXXFpviiwFLBZXO0V10oyA6nfEgQrBCm3R288q7MDlH4ZUfWf_wlGZEpt',
];

const costLineItems: CostLineItem[] = [
    {
        label: 'Prompt text',
        detail: '45–63 tokens',
        amount: '$0.0003 – $0.0004',
        isEstimate: true,
    },
    {
        label: 'Reference images',
        detail: '2 × 765 tokens',
        amount: '$0.0191',
    },
    {
        label: 'Output image',
        detail: '1024×1024 medium · 1,100 tokens',
        amount: '$0.0550',
    },
    {
        label: 'Reasoning',
        detail: '200–2,000 tokens',
        amount: '$0.0031 – $0.0313',
        isEstimate: true,
    },
];
</script>

<template>
    <Head title="Generate" />

    <div class="grid grid-cols-1 items-start gap-3 xl:grid-cols-12">
        <div class="flex flex-col gap-3 xl:col-span-8">
            <PromptEditor />
            <SizePicker :options="sizes" />
            <QualitySelector :options="qualities" />
            <ReferenceImages :urls="referenceUrls" :max-images="4" />
        </div>

        <div class="xl:sticky xl:top-16 xl:col-span-4">
            <CostEstimatePanel :line-items="costLineItems" />
        </div>
    </div>
</template>
