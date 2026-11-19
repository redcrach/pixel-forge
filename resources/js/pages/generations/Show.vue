<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import {
    ArrowLeft,
    CloudOff,
    Copy,
    Download,
    Hourglass,
    ImageIcon,
    ListOrdered,
    LoaderCircle,
    Receipt,
    SlidersHorizontal,
    Sparkles,
    Type,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import downloadGenerationImage from '@/actions/App/Http/Controllers/DownloadGenerationImageController';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    GENERATION_UPDATED_EVENT,
    generationChannel,
} from '@/lib/generationChannel';
import { toGenerationSummary } from '@/lib/generationSummary';
import { formatMoney } from '@/lib/money';
import { history, home } from '@/routes';
import type {
    GenerationHistoryItem,
    GenerationUpdatedPayload,
    ImageSize,
} from '@/types';

const props = defineProps<{
    generation: GenerationHistoryItem;
}>();

const page = usePage();
const summary = computed(() => toGenerationSummary(props.generation));

const aspectRatios: Record<ImageSize, string> = {
    '1024x1024': '1:1 · Square',
    '1536x1024': '3:2 · Landscape',
    '1024x1536': '2:3 · Portrait',
};

/** Fit shows the whole image in the panel; 100% shows it at its real size, scrollable. */
const isActualSize = ref(false);

const tokenLines = computed(() => {
    const usage = props.generation.usage;

    if (usage === null) {
        return [];
    }

    return [
        { label: 'Prompt text', tokens: usage.input_text_tokens },
        { label: 'Reference images', tokens: usage.input_image_tokens },
        { label: 'Output image', tokens: usage.output_tokens },
        { label: 'Reasoning', tokens: usage.reasoning_tokens },
    ];
});

function formatDateTime(isoDateTime: string | null): string {
    return isoDateTime === null
        ? '—'
        : new Date(isoDateTime).toLocaleString('en-GB', {
              day: 'numeric',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
              hourCycle: 'h23',
          });
}

async function copyPrompt(): Promise<void> {
    try {
        await navigator.clipboard.writeText(props.generation.prompt);
        toast.success('Prompt copied');
    } catch {
        toast.error('The prompt could not be copied.');
    }
}

/** Follows this generation live, so a queued or processing one turns into its image here. */
useEcho<GenerationUpdatedPayload>(
    generationChannel(page.props.auth.user!.id),
    GENERATION_UPDATED_EVENT,
    ({ generation }) => {
        if (generation.id === props.generation.id) {
            router.replaceProp('generation', generation);
        }
    },
);
</script>

<template>
    <Head :title="`Generation · ${summary.statusLabel}`" />

    <div class="flex flex-col gap-4">
        <div
            class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center"
        >
            <div class="flex items-center gap-2">
                <Link
                    :href="history()"
                    class="flex min-h-11 items-center gap-1 rounded border border-border-subtle bg-surface-raised px-2 py-1 font-mono text-label-default text-on-surface-variant transition-colors hover:border-border-prominent hover:text-on-surface sm:min-h-0"
                >
                    <ArrowLeft class="size-4" />
                    History
                </Link>
                <span class="font-mono text-label-default text-outline">/</span>
                <h1 class="font-display text-headline-lg text-on-surface">
                    Generation
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <StatusBadge
                    :status="generation.status"
                    :label="summary.statusLabel"
                />
                <span
                    class="rounded-full border border-border-subtle bg-surface-raised px-2 py-0.5 font-mono text-label-default tabular-nums"
                    :class="
                        generation.status === 'succeeded'
                            ? 'text-primary'
                            : 'text-on-surface-variant'
                    "
                >
                    {{ summary.cost }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-12">
            <section
                class="flex min-w-0 flex-col overflow-hidden rounded-lg border border-border-subtle bg-surface-raised xl:col-span-8"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-b border-border-subtle px-3 py-2"
                >
                    <span class="font-mono text-label-default text-on-surface">
                        {{ summary.size }} px
                        <span class="text-outline"
                            >· PNG · {{ summary.quality }}</span
                        >
                    </span>

                    <div
                        v-if="summary.imageUrl"
                        class="flex items-center gap-2"
                    >
                        <div
                            class="flex items-center gap-0.5 rounded border border-border-subtle bg-surface-container-lowest p-0.5 font-mono text-label-default"
                            role="radiogroup"
                            aria-label="Zoom"
                        >
                            <button
                                v-for="option in [
                                    { label: 'Fit', isActual: false },
                                    { label: '100%', isActual: true },
                                ]"
                                :key="option.label"
                                type="button"
                                role="radio"
                                :aria-checked="isActualSize === option.isActual"
                                class="flex min-h-11 min-w-11 items-center justify-center rounded px-2 py-0.5 transition-colors sm:min-h-0 sm:min-w-0"
                                :class="
                                    isActualSize === option.isActual
                                        ? 'bg-primary/10 text-primary'
                                        : 'text-outline hover:text-on-surface'
                                "
                                @click="isActualSize = option.isActual"
                            >
                                {{ option.label }}
                            </button>
                        </div>

                        <a
                            v-if="summary.canDownload"
                            :href="downloadGenerationImage.url(generation.id)"
                            class="flex min-h-11 items-center gap-1 rounded border border-border-prominent bg-surface-container-lowest px-2 py-1 font-mono text-label-default font-semibold text-canvas-light transition-colors hover:border-primary hover:text-primary sm:min-h-0"
                        >
                            <Download class="size-4" />
                            Download PNG
                        </a>
                    </div>
                </div>

                <div
                    class="flex min-h-64 items-center-safe justify-center-safe bg-surface-container-lowest sm:min-h-[420px]"
                    :class="
                        isActualSize
                            ? 'max-h-[75vh] overflow-auto'
                            : 'p-2 sm:p-4'
                    "
                >
                    <img
                        v-if="summary.imageUrl"
                        :src="summary.imageUrl"
                        :alt="generation.prompt"
                        class="rounded"
                        :class="
                            isActualSize
                                ? 'max-w-none'
                                : 'max-h-[70vh] w-auto object-contain'
                        "
                    />

                    <div
                        v-else-if="generation.status === 'processing'"
                        class="flex flex-col items-center gap-2 text-center"
                    >
                        <div
                            class="flex size-12 items-center justify-center rounded-full border border-amber-400/30 bg-amber-400/10"
                        >
                            <LoaderCircle
                                class="size-6 animate-spin text-amber-400"
                            />
                        </div>
                        <p class="text-body-default text-on-surface">
                            Generating your image…
                        </p>
                        <p class="text-label-dense text-outline">
                            It appears here as soon as it's ready.
                        </p>
                    </div>

                    <div
                        v-else-if="
                            generation.status === 'queued' ||
                            generation.status === 'pending'
                        "
                        class="flex flex-col items-center gap-2 text-center"
                    >
                        <div
                            class="flex size-12 items-center justify-center rounded-full border border-tertiary/30 bg-tertiary/10 text-tertiary"
                        >
                            <ListOrdered
                                v-if="generation.status === 'queued'"
                                class="size-6"
                            />
                            <Hourglass v-else class="size-6" />
                        </div>
                        <p class="text-body-default text-on-surface">
                            {{ summary.statusLabel }}
                        </p>
                        <p class="text-label-dense text-outline">
                            It appears here as soon as it's ready.
                        </p>
                    </div>

                    <div
                        v-else-if="summary.isExpired"
                        class="flex flex-col items-center gap-2 text-center"
                    >
                        <CloudOff class="size-8 text-outline" />
                        <p class="text-body-default text-on-surface">
                            Image expired
                        </p>
                        <p class="text-label-dense text-outline">
                            Pixelforge keeps images for 24 hours after they
                            complete.
                        </p>
                    </div>

                    <div
                        v-else-if="summary.errorMessage"
                        class="flex max-w-md flex-col items-center gap-2 text-center"
                    >
                        <p
                            class="font-mono text-label-default font-semibold tracking-[0.04em] text-error uppercase"
                        >
                            {{ summary.errorMessage }}
                        </p>
                        <p class="font-mono text-mono-badge text-outline">
                            {{ summary.errorCode }} · Not charged
                        </p>
                    </div>

                    <ImageIcon v-else class="size-8 text-outline" />
                </div>
            </section>

            <aside class="flex min-w-0 flex-col gap-3 xl:col-span-4">
                <Link
                    :href="home({ query: { remix: generation.id } })"
                    class="flex items-center justify-center gap-1 rounded bg-primary py-3 font-display text-headline-sm font-bold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)] transition-all hover:bg-primary-container hover:shadow-[0_0_18px_rgba(154,230,0,0.4)]"
                >
                    <Sparkles class="size-4.5" />
                    Remix this prompt
                </Link>

                <section
                    class="flex flex-col gap-2 rounded-lg border border-border-subtle bg-surface-raised p-3"
                >
                    <div class="flex items-center justify-between">
                        <h2
                            class="flex items-center gap-1 font-display text-headline-sm text-on-surface"
                        >
                            <Type class="size-4 text-primary" />
                            Prompt
                        </h2>
                        <button
                            type="button"
                            class="flex min-h-11 items-center gap-1 px-1 font-mono text-label-default text-primary hover:underline sm:min-h-0 sm:px-0"
                            @click="copyPrompt"
                        >
                            <Copy class="size-3.5" />
                            Copy
                        </button>
                    </div>
                    <p
                        class="font-mono text-body-dense leading-relaxed whitespace-pre-wrap text-on-surface"
                    >
                        {{ generation.prompt }}
                    </p>
                </section>

                <section
                    class="flex flex-col gap-2 rounded-lg border border-border-subtle bg-surface-raised p-3"
                >
                    <h2
                        class="flex items-center gap-1 font-display text-headline-sm text-on-surface"
                    >
                        <SlidersHorizontal class="size-4 text-tertiary" />
                        Settings
                    </h2>
                    <dl
                        class="grid grid-cols-1 gap-x-3 gap-y-2 min-[400px]:grid-cols-2"
                    >
                        <div
                            v-for="setting in [
                                { label: 'Size', value: summary.size },
                                {
                                    label: 'Aspect ratio',
                                    value: aspectRatios[generation.size],
                                },
                                { label: 'Quality', value: summary.quality },
                                {
                                    label: 'Reference images',
                                    value: String(
                                        generation.reference_image_count,
                                    ),
                                },
                                {
                                    label: 'Created',
                                    value: formatDateTime(
                                        generation.created_at,
                                    ),
                                },
                                {
                                    label: 'Completed',
                                    value: formatDateTime(
                                        generation.completed_at,
                                    ),
                                },
                            ]"
                            :key="setting.label"
                        >
                            <dt class="text-label-dense text-outline">
                                {{ setting.label }}
                            </dt>
                            <dd
                                class="font-mono text-label-default text-on-surface"
                            >
                                {{ setting.value }}
                            </dd>
                        </div>
                    </dl>
                    <ul
                        v-if="generation.reference_images.length"
                        class="flex flex-col gap-1 border-t border-border-subtle pt-2"
                    >
                        <li
                            v-for="url in generation.reference_images"
                            :key="url"
                            class="truncate font-mono text-[11px] text-on-surface-variant"
                            :title="url"
                        >
                            {{ url }}
                        </li>
                    </ul>
                </section>

                <section
                    class="flex flex-col gap-2 rounded-lg border border-border-subtle bg-surface-raised p-3"
                >
                    <div class="flex items-center justify-between">
                        <h2
                            class="flex items-center gap-1 font-display text-headline-sm text-on-surface"
                        >
                            <Receipt class="size-4 text-primary" />
                            Billing
                        </h2>
                        <span
                            class="rounded-full border border-primary/30 bg-primary/10 px-2 py-0.5 font-mono text-label-default text-primary tabular-nums"
                        >
                            {{ summary.cost }}
                        </span>
                    </div>
                    <div
                        class="flex items-center justify-between text-label-default"
                    >
                        <span class="text-outline">Estimated</span>
                        <span
                            class="font-mono text-on-surface-variant tabular-nums"
                        >
                            {{ summary.estimate }}
                        </span>
                    </div>
                    <div
                        v-if="generation.final_price_nano !== null"
                        class="flex items-center justify-between text-label-default"
                    >
                        <span class="text-outline">Charged exactly</span>
                        <span class="font-mono text-on-surface tabular-nums">
                            {{ formatMoney(generation.final_price_nano, 9) }}
                        </span>
                    </div>
                    <ul
                        v-if="tokenLines.length"
                        class="flex flex-col divide-y divide-border-subtle border-t border-border-subtle"
                    >
                        <li
                            v-for="line in tokenLines"
                            :key="line.label"
                            class="flex items-center justify-between py-1.5 text-label-default"
                        >
                            <span class="text-on-surface-variant">
                                {{ line.label }}
                            </span>
                            <span
                                class="font-mono text-on-surface tabular-nums"
                            >
                                {{ line.tokens.toLocaleString() }} tokens
                            </span>
                        </li>
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</template>
