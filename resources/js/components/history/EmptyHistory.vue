<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ImageOff, Sparkles } from '@lucide/vue';
import { home } from '@/routes';
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl bg-surface-container-lowest p-6 text-center"
    >
        <div
            class="mb-2 flex size-14 items-center justify-center rounded-2xl bg-surface-container text-outline"
        >
            <ImageOff class="size-8" />
        </div>
        <h3 class="text-headline-lg text-on-surface">No generations yet</h3>
        <p class="mt-1 mb-3 max-w-md text-body-default text-on-surface-variant">
            Your generated images will show up here.
        </p>
        <Link
            :href="home()"
            class="flex items-center gap-0.5 rounded-lg bg-primary px-4 py-1 text-label-default text-primary-foreground transition-colors hover:bg-primary-container"
        >
            <Sparkles class="size-4.5" />
            Generate an image
        </Link>
    </div>
</template>
