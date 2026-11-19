<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ImageOff, Sparkles } from '@lucide/vue';
import { home } from '@/routes';
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-lg border border-border-subtle bg-surface-raised px-6 py-12 text-center"
    >
        <div
            class="mb-3 flex size-14 items-center justify-center rounded-lg border border-primary/30 bg-primary/10 text-primary"
        >
            <ImageOff class="size-8" />
        </div>
        <h3 class="font-display text-headline-lg text-on-surface">
            No generations yet
        </h3>
        <p class="mt-1 mb-3 max-w-md text-body-default text-on-surface-variant">
            Your generated images will show up here.
        </p>
        <Link
            :href="home()"
            class="flex items-center gap-1 rounded bg-primary px-4 py-2 text-label-default font-semibold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)] transition-colors hover:bg-primary-container"
        >
            <Sparkles class="size-4.5" />
            Generate an image
        </Link>
    </div>
</template>
