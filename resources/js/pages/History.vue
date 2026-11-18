<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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
    { label: 'All', count: 18, isActive: true },
    { label: 'In progress', count: 3 },
    { label: 'Succeeded', count: 13 },
    { label: 'Failed', count: 2 },
];

const generations: GenerationSummary[] = [
    {
        id: '1047',
        prompt: 'Hyperrealistic portrait of an astronaut on a neon mars base with holographic HUD overlay, cinematic lighting',
        status: 'processing',
        statusLabel: 'Processing · 14s',
        size: '1024×1024',
        quality: 'High',
        estimate: '$0.2500 – $0.3595',
        cost: 'Pending',
        createdAgo: 'Just now',
    },
    {
        id: '1046',
        prompt: 'Isometric floating island cyberpunk garden with bioluminescent waterfalls, mist clouds, night setting',
        status: 'pending',
        statusLabel: 'Waiting for slot',
        size: '1536×1024',
        quality: 'Medium',
        estimate: '$0.0857 – $0.1140',
        cost: 'Pending',
        createdAgo: '1m ago',
    },
    {
        id: '1045',
        prompt: 'Studio product shot of a luxury obsidian fragrance bottle with violet vapor swirling inside, glossy reflections',
        status: 'queued',
        statusLabel: 'Queued',
        size: '1024×1536',
        quality: 'High',
        estimate: '$0.3384 – $0.4479',
        cost: 'Pending',
        createdAgo: '2m ago',
    },
    {
        id: '1044',
        prompt: 'Cinematic wide angle shot of an obsidian brutalist monolithic laboratory floating in misty arctic waters, moody volumetric lighting',
        status: 'succeeded',
        statusLabel: 'Succeeded',
        size: '1024×1024',
        quality: 'Medium',
        imageUrl:
            'https://lh3.googleusercontent.com/aida-public/AB6AXuAbFOhfc5ehFgqpBsPZe6us_2yhWFkKT__EJqve0LQlOiQRTeh8p-uj7E0_RTk1LLYz5vnOBU48wO5ieX5w09EdAat5BudNbUIPiZu30Zh5-G-0BXYIRyH1MnfIP8h5CwYG1IkAcUSU2KS-aosjRVUoEMNiDvpuM87lqjlVIxgVHemuYaLJn9XYCNfrPG_MUyIGcnRI8NPy8mpTxTlGTsOWmY_0MfrW9t73VzAA4739',
        estimate: '$0.0775 – $0.1058',
        cost: '$0.0929',
        createdAgo: '12m ago',
    },
    {
        id: '1041',
        prompt: 'Explosion of classified schematic blueprints flying across a war room with red emergency strobe lights',
        status: 'failed',
        statusLabel: 'Failed',
        size: '1024×1024',
        quality: 'Medium',
        errorMessage: 'Generation failed',
        errorCode: 'generation_failed',
        estimate: '$0.0587 – $0.0868',
        cost: 'Not charged',
        createdAgo: '2h ago',
    },
    {
        id: '1039',
        prompt: 'Macro crystalline prism refraction inside a hollow sphere, hyper-detailed caustics, chromatic aberration',
        status: 'succeeded',
        statusLabel: 'Succeeded',
        size: '1024×1024',
        quality: 'Low',
        isExpired: true,
        estimate: '$0.0143 – $0.0222',
        cost: '$0.0171',
        createdAgo: '34d ago',
    },
];

const selectedGeneration: GenerationDetails = {
    id: '1044',
    status: 'succeeded',
    statusLabel: 'Succeeded',
    createdAt: 'Nov 18, 2026 · 14:22 UTC',
    imageUrl:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuAAlsVWmfnrvIc_EQ2YoZMUwql0ZbjJnCGD78txOupRUytLz6j4IFpPhTHGfC3ghHMtodiY2034NrK31_q5L4WOErUAq9fsxF300DD8hwt9TjvxxcisosKe8s9SH4ZdY4fKRLH_UjWC3otBI2vwOXIjwBe245znRRSxd0TkeCzKpY7i5f8Swo2xzHkSkicmaqTvQUF9XJEEuVeiX_qgm59o9bMAN81AahxAXlE0Odwg',
    imageMeta: 'PNG · 1024×1024',
    prompt: 'Cinematic wide angle shot of an obsidian brutalist monolithic laboratory floating in misty arctic waters, moody volumetric lighting, sharp rim illumination, anamorphic lens flare, 8k resolution, photorealistic render',
    settings: '1024×1024 · Medium · 2 reference images',
    costRows: [
        {
            phase: 'Prompt text (52 tokens)',
            estimated: '$0.0003 – $0.0004',
            actual: '$0.0003',
        },
        {
            phase: 'Reference images (2)',
            estimated: '$0.0191',
            actual: '$0.0191',
        },
        {
            phase: 'Output image (1,100 tokens)',
            estimated: '$0.0550',
            actual: '$0.0550',
        },
        {
            phase: 'Reasoning (1,180 tokens)',
            estimated: '$0.0031 – $0.0313',
            actual: '$0.0184',
        },
    ],
    totalEstimated: '$0.0775 – $0.1058',
    totalActual: '$0.0929',
    exactCharge: '$0.092887500',
    releasedHold: '$0.0129',
    timeline: [
        {
            title: 'Submitted',
            description: '$0.1058 held from your balance',
            timestamp: '14:22:00.912',
            tone: 'muted',
        },
        {
            title: 'Queued',
            description: 'Accepted by Pixelforge',
            timestamp: '14:22:01.420',
            tone: 'muted',
        },
        {
            title: 'Processing',
            timestamp: '14:22:03.180',
            tone: 'processing',
        },
        {
            title: 'Succeeded',
            description: 'Image saved',
            timestamp: '14:22:15.890',
            tone: 'succeeded',
        },
        {
            title: 'Charged',
            description: '$0.0929 charged, $0.0129 returned to your balance',
            timestamp: '14:22:15.902',
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
            <div class="flex flex-col gap-0.5">
                <h1 class="text-headline-xl tracking-tight text-on-surface">
                    History
                </h1>
                <p class="text-body-default text-on-surface-variant">
                    Your generations, their status and what they cost.
                </p>
            </div>

            <HistoryFilterBar :tabs="filterTabs" />
        </div>

        <EmptyHistory v-if="generations.length === 0" />

        <div
            v-else
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
    </div>
</template>
