<template>
    <section
        class="flex flex-col gap-1 rounded-lg border border-outline-variant/30 bg-surface-container-lowest p-2"
    >
        <div
            class="flex items-center justify-between text-label-dense text-outline"
        >
            <span class="font-semibold tracking-wider uppercase">
                Inference Pipeline
            </span>
            <span class="font-mono text-tertiary">Cluster EU-North #04</span>
        </div>
        <div
            class="flex items-center justify-between text-body-dense text-on-surface-variant"
        >
            <span>Latency target</span>
            <span class="font-mono text-on-surface">~4.2s to first pixel</span>
        </div>
        <div
            class="flex items-center justify-between text-body-dense text-on-surface-variant"
        >
            <span>Engine checkpoint</span>
            <span class="font-mono text-on-surface"
                >sdxl-turbo-refine-v2.4</span
            >
        </div>
    </section>
</template>
