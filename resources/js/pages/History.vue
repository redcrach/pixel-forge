<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BoxSelect, Download } from '@lucide/vue';
import { ref } from 'vue';
import EmptyHistory from '@/components/history/EmptyHistory.vue';
import GenerationCard from '@/components/history/GenerationCard.vue';
import GenerationDetailsDrawer from '@/components/history/GenerationDetailsDrawer.vue';
import HistoryFilterBar from '@/components/history/HistoryFilterBar.vue';
import type {
    GenerationDetails,
    GenerationSummary,
    HistoryFilterTab,
} from '@/types';

const filterTabs: HistoryFilterTab[] = [
    { label: 'All', count: 18, tone: 'primary', isActive: true },
    { label: 'Processing', count: 2, tone: 'processing' },
    { label: 'Succeeded', count: 14, tone: 'succeeded' },
    { label: 'Failed', count: 2, tone: 'failed' },
];

const selectedImageUrl =
    'https://lh3.googleusercontent.com/aida-public/AB6AXuAbFOhfc5ehFgqpBsPZe6us_2yhWFkKT__EJqve0LQlOiQRTeh8p-uj7E0_RTk1LLYz5vnOBU48wO5ieX5w09EdAat5BudNbUIPiZu30Zh5-G-0BXYIRyH1MnfIP8h5CwYG1IkAcUSU2KS-aosjRVUoEMNiDvpuM87lqjlVIxgVHemuYaLJn9XYCNfrPG_MUyIGcnRI8NPy8mpTxTlGTsOWmY_0MfrW9t73VzAA4739';

const generations: GenerationSummary[] = [
    {
        id: 'PF-88422',
        prompt: 'Hyperrealistic portrait of an astronaut on a neon mars base with holographic HUD overlay, cinematic lighting, octane render...',
        status: 'processing',
        statusLabel: 'Processing • 14s',
        tags: [
            { label: '1024×1024' },
            { label: 'High' },
            { label: 'SDXL v2.4', tone: 'model' },
        ],
        costLabel: 'Est: $0.068 – $0.096',
        costValue: 'Pending...',
        isCostPending: true,
        createdAgo: 'Just now (14s ago)',
    },
    {
        id: 'PF-88421',
        prompt: 'Isometric floating island cyberpunk garden with bioluminescent waterfalls, mist clouds, detailed vegetation, night setting...',
        status: 'waiting',
        statusLabel: 'Waiting for slot',
        tags: [{ label: '1536×1024' }, { label: 'Medium' }],
        costLabel: 'Est: $0.052 – $0.074',
        costValue: '$0.0000',
        createdAgo: '1m ago',
    },
    {
        id: 'PF-88420',
        prompt: 'Studio product shot of luxury obsidian fragrance bottle with violet vapor swirling inside, glossy reflections, dark studio cyclorama...',
        status: 'queued',
        statusLabel: 'Queued • #3 Priority',
        tags: [{ label: '1024×1536' }, { label: 'High' }],
        costLabel: 'Est: $0.071 – $0.098',
        costValue: 'Pending slot',
        createdAgo: '2m ago',
    },
    {
        id: 'PF-88419',
        prompt: 'Brutalist monolithic laboratory floating in arctic waters, volumetric fog, dramatic obsidian surfaces, stark linear architecture...',
        status: 'succeeded',
        statusLabel: 'Succeeded',
        imageUrl: selectedImageUrl,
        tags: [
            { label: '1024×1024' },
            { label: 'High' },
            { label: 'FLUX.1-Pro', tone: 'primary' },
        ],
        costLabel: 'Est: $0.068 – $0.096',
        costValue: '$0.0812',
        createdAgo: '12m ago',
    },
    {
        id: 'PF-87910',
        prompt: 'Macro crystalline prism refraction inside hollow sphere, hyper-detailed caustics, monochromatic chromatic aberration...',
        status: 'succeeded',
        statusLabel: 'Succeeded',
        isExpired: true,
        tags: [{ label: '1024×1024' }, { label: 'Medium' }],
        costLabel: 'Paid Archive',
        costValue: '$0.0480',
        createdAgo: '34d ago',
    },
    {
        id: 'PF-88415',
        prompt: 'Explosion of classified schematic blueprints flying across war room with red emergency alarm strobe lights, high tension...',
        status: 'failed',
        statusLabel: 'Failed',
        failureReason: 'Safety Filter Violation',
        failureCode: 'PROHIBITED_CONTENT_DETECTED',
        tags: [
            { label: '1024×1024' },
            { label: 'Not Charged ($0.0000)', tone: 'success' },
        ],
        costLabel: 'Refund Status',
        costValue: '0.00 credits',
        createdAgo: '2h ago',
    },
];

const selectedGeneration: GenerationDetails = {
    id: 'PF-88419',
    status: 'succeeded',
    statusLabel: 'Succeeded',
    createdAt: 'Oct 24, 2024 • 14:22:01 UTC',
    imageUrl:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuAAlsVWmfnrvIc_EQ2YoZMUwql0ZbjJnCGD78txOupRUytLz6j4IFpPhTHGfC3ghHMtodiY2034NrK31_q5L4WOErUAq9fsxF300DD8hwt9TjvxxcisosKe8s9SH4ZdY4fKRLH_UjWC3otBI2vwOXIjwBe245znRRSxd0TkeCzKpY7i5f8Swo2xzHkSkicmaqTvQUF9XJEEuVeiX_qgm59o9bMAN81AahxAXlE0Odwg',
    imageMeta: 'PNG • 1024×1024 • 4.2 MB',
    prompt: 'Brutalist monolithic laboratory floating in arctic waters, volumetric fog, dramatic obsidian surfaces, stark linear architecture, glowing violet interior apertures, moody cinematic atmosphere, shot on 35mm Hasselblad, 8k resolution.',
    seed: '48819203',
    sampler: 'DPM++ 2M Karras',
    steps: 30,
    savedLabel: 'Saved $0.0148 vs max',
    costRows: [
        {
            phase: 'Input Text (58 tokens)',
            estimated: '$0.0014',
            actual: '$0.0014',
        },
        {
            phase: 'Reference Latents (2 imgs)',
            estimated: '$0.0240',
            actual: '$0.0240',
        },
        { phase: 'Base Render Pass', estimated: '$0.0400', actual: '$0.0400' },
        {
            phase: 'Reasoning Steps (2 steps)',
            estimated: '$0.0306',
            actual: '$0.0158',
            isSaving: true,
        },
    ],
    totalEstimated: '$0.0960',
    totalActual: '$0.0812',
    timeline: [
        {
            title: 'Job created & validated',
            description: 'Parameters passed syntax check',
            timestamp: '14:22:01.104',
            tone: 'muted',
        },
        {
            title: 'Queued in inference slot',
            description: 'Assigned node flux-gpu-h100-09',
            timestamp: '14:22:01.420',
            tone: 'muted',
        },
        {
            title: 'Processing started',
            description: 'Pass 1 diffusion • 30 inference steps',
            timestamp: '14:22:02.180',
            tone: 'processing',
        },
        {
            title: 'Generation succeeded',
            description: 'Latents decoded, 1024px tensor cached',
            timestamp: '14:22:15.890',
            tone: 'succeeded',
        },
        {
            title: 'USD credits deducted',
            description: '-$0.0812 settled from balance',
            timestamp: '14:22:16.012',
            tone: 'primary',
        },
    ],
};

const isDrawerOpen = ref(true);
</script>

<template>
    <Head title="History" />

    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-3">
            <div
                class="flex flex-col justify-between gap-2 xl:flex-row xl:items-end"
            >
                <div class="flex flex-col gap-0.5">
                    <div class="flex items-center gap-1">
                        <span
                            class="rounded bg-surface-container-high px-0.5 py-0.5 font-mono text-mono-badge tracking-wider text-outline uppercase"
                        >
                            Cluster: us-east-inference-04
                        </span>
                        <span class="font-mono text-mono-badge text-tertiary">
                            Live Telemetry
                        </span>
                    </div>
                    <h1 class="text-headline-xl tracking-tight text-on-surface">
                        Generation History
                    </h1>
                    <p
                        class="max-w-2xl text-body-default text-on-surface-variant"
                    >
                        Review past jobs, inspect prompt parameters, token
                        expenditure, and download high-resolution outputs.
                    </p>
                </div>

                <div class="flex items-center gap-1 self-start xl:self-auto">
                    <button
                        type="button"
                        class="flex items-center gap-0.5 rounded-lg bg-surface-container px-2 py-0.5 text-label-default text-on-surface transition-colors hover:bg-surface-container-high"
                    >
                        <BoxSelect class="size-4" />
                        Select All
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-0.5 rounded-lg bg-surface-container px-2 py-0.5 text-label-default text-on-surface transition-colors hover:bg-surface-container-high"
                    >
                        <Download class="size-4" />
                        Export CSV
                    </button>
                </div>
            </div>

            <HistoryFilterBar :tabs="filterTabs" />
        </div>

        <div
            class="relative flex w-full flex-col items-start gap-3 xl:flex-row"
        >
            <div
                class="grid w-full min-w-0 flex-1 grid-cols-1 gap-2 md:grid-cols-2 2xl:grid-cols-3"
            >
                <GenerationCard
                    v-for="generation in generations"
                    :key="generation.id"
                    :generation="generation"
                    :is-selected="
                        isDrawerOpen && generation.id === selectedGeneration.id
                    "
                />
            </div>

            <GenerationDetailsDrawer
                v-if="isDrawerOpen"
                :generation="selectedGeneration"
                @close="isDrawerOpen = false"
            />
        </div>

        <EmptyHistory class="mt-12" />
    </div>
</template>
