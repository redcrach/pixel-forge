<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ImageOff, Sparkles } from '@lucide/vue';
import { home } from '@/routes';
</script>

<template>
    <div
        class="flex flex-col items-center justify-center border-2 border-border-subtle bg-surface-raised px-6 py-12 text-center shadow-pixel"
    >
        <div
            class="mb-3 flex size-14 items-center justify-center bg-surface-container-lowest text-wheat shadow-pixel-inset"
        >
            <ImageOff class="size-8" />
        </div>
        <span
            class="font-mono text-label-default font-bold text-primary uppercase"
        >
            [ Ready for input ]
        </span>
        <h3
            class="font-display text-headline-lg font-bold text-on-surface uppercase"
        >
            No generations yet
        </h3>
        <p class="mt-1 mb-3 max-w-md text-body-default text-on-surface-variant">
            Your generated images will show up here.
        </p>
        <Link
            :href="home()"
            class="pixel-press flex items-center gap-1 bg-primary px-4 py-2 text-label-default font-bold text-primary-foreground uppercase transition-colors hover:bg-primary-container"
        >
            <Sparkles class="size-4.5" />
            [&gt; Generate an image]
        </Link>
    </div>
</template>
