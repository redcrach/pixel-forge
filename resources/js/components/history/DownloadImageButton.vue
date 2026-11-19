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
        class="relative z-20 flex size-11 shrink-0 items-center justify-center rounded border border-border-prominent bg-surface-raised text-[#a3a3a3] transition-colors hover:border-primary hover:text-primary sm:size-8"
        @click.stop
    >
        <Download class="size-4" />
        <span class="sr-only">Download image</span>
    </a>
</template>
