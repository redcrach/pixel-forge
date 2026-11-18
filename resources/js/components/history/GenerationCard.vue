<script setup lang="ts">
import {
    CloudOff,
    Hourglass,
    Info,
    ListOrdered,
    LoaderCircle,
    ShieldX,
} from '@lucide/vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { GenerationSummary, GenerationTag } from '@/types';

defineProps<{
    generation: GenerationSummary;
    isSelected?: boolean;
}>();

const tagToneClasses: Record<NonNullable<GenerationTag['tone']>, string> = {
    default: 'bg-surface-container-high text-on-surface-variant',
    model: 'bg-surface-container-high text-tertiary',
    primary: 'bg-primary-container/20 text-primary',
    success: 'bg-green-500/10 text-green-400',
};
</script>

<template>
    <article
        class="group relative flex cursor-pointer flex-col overflow-hidden rounded-xl transition-all"
        :class="
            isSelected
                ? 'bg-surface-container shadow-xl ring-2 shadow-primary/5 ring-primary'
                : 'bg-surface-container-low hover:bg-surface-container'
        "
    >
        <div
            class="relative flex aspect-video w-full items-center justify-center overflow-hidden bg-surface-container-lowest"
        >
            <template v-if="generation.imageUrl">
                <img
                    :src="generation.imageUrl"
                    :alt="generation.prompt"
                    class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div
                    class="absolute inset-0 bg-linear-to-t from-surface-container-lowest via-transparent to-transparent opacity-80"
                />
            </template>

            <div
                v-else-if="generation.status === 'processing'"
                class="flex flex-col items-center gap-1"
            >
                <div
                    class="absolute inset-0 animate-shimmer bg-linear-to-r from-transparent via-surface-container-high/40 to-transparent"
                />
                <div
                    class="relative flex size-8 items-center justify-center rounded-full bg-amber-500/10"
                >
                    <LoaderCircle class="size-5 animate-spin text-amber-400" />
                </div>
                <StatusBadge
                    class="relative"
                    :status="generation.status"
                    :label="generation.statusLabel"
                />
            </div>

            <template v-else-if="generation.status === 'waiting'">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,var(--color-on-surface-variant)_1px,transparent_0)] bg-size-[16px_16px] opacity-15"
                />
                <div
                    class="relative flex flex-col items-center gap-1"
                    title="Provider busy, retrying automatically"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-full bg-surface-container-high text-outline"
                    >
                        <Hourglass class="size-5" />
                    </div>
                    <StatusBadge
                        :status="generation.status"
                        :label="generation.statusLabel"
                    />
                </div>
                <span
                    class="absolute top-2 right-2 text-outline"
                    title="Provider busy, retrying automatically"
                >
                    <Info class="size-4" />
                </span>
            </template>

            <div
                v-else-if="generation.status === 'queued'"
                class="flex flex-col items-center gap-1"
            >
                <div
                    class="flex size-8 items-center justify-center rounded-full bg-tertiary-container/20 text-tertiary"
                >
                    <ListOrdered class="size-5" />
                </div>
                <StatusBadge
                    :status="generation.status"
                    :label="generation.statusLabel"
                />
            </div>

            <div
                v-else-if="generation.isExpired"
                class="flex flex-col items-center p-2 text-center"
            >
                <div
                    class="mb-0.5 flex size-9 items-center justify-center rounded-full bg-surface-container-high text-outline"
                >
                    <CloudOff class="size-5" />
                </div>
                <span class="text-body-dense font-medium text-on-surface">
                    Image expired
                </span>
                <p
                    class="mt-1 max-w-[200px] text-label-dense leading-tight text-outline"
                >
                    Original asset cleared after 30 days retention. Metadata
                    preserved.
                </p>
            </div>

            <template v-else-if="generation.status === 'failed'">
                <div
                    class="absolute inset-0 bg-[repeating-linear-gradient(45deg,var(--color-error),var(--color-error)_10px,transparent_10px,transparent_20px)] opacity-10"
                />
                <div
                    class="relative flex flex-col items-center gap-0.5 p-2 text-center"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-full bg-rose-500/10 text-rose-400"
                    >
                        <ShieldX class="size-5" />
                    </div>
                    <span
                        class="font-mono text-mono-badge font-semibold text-rose-400 uppercase"
                    >
                        {{ generation.failureReason }}
                    </span>
                    <span class="text-label-dense text-outline">
                        Code: {{ generation.failureCode }}
                    </span>
                </div>
            </template>

            <div class="absolute top-2 left-2 flex items-center gap-0.5">
                <span
                    class="rounded bg-surface-container-lowest/80 px-0.5 py-0.5 font-mono text-mono-badge"
                    :class="isSelected ? 'text-primary' : 'text-outline'"
                >
                    #{{ generation.id }}
                </span>
                <span
                    v-if="isSelected"
                    class="rounded bg-primary px-0.5 py-0.5 font-mono text-mono-badge text-primary-foreground"
                >
                    Active Inspection
                </span>
            </div>
            <StatusBadge
                v-if="
                    generation.status === 'succeeded' ||
                    generation.status === 'failed'
                "
                class="absolute top-2 right-2 backdrop-blur-sm"
                :status="generation.status"
                :label="generation.statusLabel"
            />
        </div>

        <div class="flex flex-1 flex-col justify-between gap-2 p-2">
            <div class="flex flex-col gap-0.5">
                <p
                    class="line-clamp-2 text-body-dense"
                    :class="
                        generation.isExpired
                            ? 'text-on-surface-variant'
                            : 'text-on-surface'
                    "
                >
                    {{ generation.prompt }}
                </p>
                <div class="flex flex-wrap items-center gap-0.5 pt-0.5">
                    <span
                        v-for="tag in generation.tags"
                        :key="tag.label"
                        class="rounded px-0.5 py-0.5 font-mono text-mono-badge"
                        :class="tagToneClasses[tag.tone ?? 'default']"
                    >
                        {{ tag.label }}
                    </span>
                </div>
            </div>

            <div
                class="-mx-2 -mb-2 flex items-end justify-between rounded-b-xl bg-surface-container-lowest/40 p-2"
            >
                <div class="flex flex-col">
                    <span class="text-label-dense text-outline">
                        {{ generation.costLabel }}
                    </span>
                    <span
                        class="flex items-center gap-1 font-mono text-mono-metric tabular-nums"
                        :class="{
                            'text-amber-400': generation.isCostPending,
                            'font-semibold text-on-surface':
                                !generation.isCostPending && isSelected,
                            'text-on-surface-variant':
                                !generation.isCostPending && !isSelected,
                        }"
                    >
                        <span
                            v-if="generation.isCostPending"
                            class="size-1.5 rounded-full bg-amber-400"
                        />
                        {{ generation.costValue }}
                    </span>
                </div>
                <span class="text-label-dense text-outline">
                    {{ generation.createdAgo }}
                </span>
            </div>
        </div>
    </article>
</template>
