<script setup lang="ts">
import type { TimelineEvent } from '@/types';

defineProps<{
    events: TimelineEvent[];
}>();

const dotToneClasses: Record<TimelineEvent['tone'], string> = {
    muted: 'bg-surface-container-highest',
    processing: 'bg-amber-400',
    succeeded: 'bg-green-400',
    primary: 'bg-primary',
};
</script>

<template>
    <div class="flex flex-col gap-1">
        <h3 class="text-headline-sm text-on-surface">Timeline</h3>

        <ol class="relative flex flex-col gap-2 pl-6">
            <span
                class="absolute top-2 bottom-2 left-2 w-0.5 bg-surface-container-highest"
            />
            <li
                v-for="event in events"
                :key="event.timestamp"
                class="relative flex flex-col gap-0.5"
            >
                <span
                    class="absolute top-1 -left-6 size-2.5 rounded-full ring-4 ring-surface-container-low"
                    :class="dotToneClasses[event.tone]"
                />
                <div class="flex items-center justify-between gap-2">
                    <span class="text-body-dense text-on-surface">
                        {{ event.title }}
                    </span>
                    <span class="font-mono text-mono-badge text-outline">
                        {{ event.timestamp }}
                    </span>
                </div>
                <span
                    v-if="event.description"
                    class="text-label-dense"
                    :class="
                        event.tone === 'primary'
                            ? 'text-primary'
                            : 'text-outline'
                    "
                >
                    {{ event.description }}
                </span>
            </li>
        </ol>
    </div>
</template>
