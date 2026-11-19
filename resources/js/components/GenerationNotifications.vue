<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { toast } from 'vue-sonner';
import {
    GENERATION_UPDATED_EVENT,
    generationChannel,
} from '@/lib/generationChannel';
import { isFailedStatus } from '@/lib/generationSummary';
import { history } from '@/routes';
import type { GenerationUpdatedPayload } from '@/types';

/**
 * Shows a toast on any page when one of the user's generations finishes.
 * Renders nothing: it only listens to the user's private channel.
 */
const props = defineProps<{
    userId: number;
}>();

/** A redelivered or repeated event must not toast twice for the same generation. */
const notifiedGenerationIds = new Set<string>();

function viewHistoryAction() {
    return { label: 'View', onClick: () => router.visit(history()) };
}

useEcho<GenerationUpdatedPayload>(
    generationChannel(props.userId),
    GENERATION_UPDATED_EVENT,
    ({ generation }) => {
        const isFinished =
            generation.status === 'succeeded' ||
            isFailedStatus(generation.status);

        if (!isFinished || notifiedGenerationIds.has(generation.id)) {
            return;
        }

        notifiedGenerationIds.add(generation.id);

        if (generation.status === 'succeeded') {
            toast.success('Your image is ready', {
                description: generation.prompt,
                action: viewHistoryAction(),
            });

            return;
        }

        toast.error('Generation failed · not charged', {
            description: generation.error?.message,
            action: viewHistoryAction(),
        });
    },
);
</script>
