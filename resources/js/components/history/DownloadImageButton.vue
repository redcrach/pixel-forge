<script setup lang="ts">
import { Download } from '@lucide/vue';
import downloadGenerationImage from '@/actions/App/Http/Controllers/DownloadGenerationImageController';

/**
 * A plain link (not an Inertia visit): the server answers with the PNG as an attachment,
 * so the browser saves it and the page stays where it is.
 */
defineProps<{
    generationId: string;
}>();
</script>

<template>
    <a
        :href="downloadGenerationImage.url(generationId)"
        title="Download image"
        class="pixel-press relative z-20 flex size-11 shrink-0 items-center justify-center bg-primary text-primary-foreground transition-colors hover:bg-primary-container sm:size-8"
        @click.stop
    >
        <Download class="size-4" />
        <span class="sr-only">Download image</span>
    </a>
</template>
