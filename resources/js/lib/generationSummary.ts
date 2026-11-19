import { formatTimeAgo } from '@vueuse/core';
import { formatMoney, formatMoneyRange } from '@/lib/money';
import type {
    GenerationHistoryItem,
    GenerationStatus,
    GenerationSummary,
    HistoryFilters,
    HistoryStatusFilter,
    ImageQuality,
    ImageSize,
} from '@/types';

const statusLabels: Record<GenerationStatus, string> = {
    pending: 'Waiting for slot',
    submission_failed: 'Failed',
    queued: 'Queued',
    processing: 'Processing',
    succeeded: 'Succeeded',
    failed: 'Failed',
};

const shapeLabels: Record<ImageSize, string> = {
    '1024x1024': 'Square',
    '1536x1024': 'Landscape',
    '1024x1536': 'Portrait',
};

const qualityLabels: Record<ImageQuality, string> = {
    low: 'Low',
    medium: 'Medium',
    high: 'High',
};

export function isFailedStatus(status: GenerationStatus): boolean {
    return status === 'failed' || status === 'submission_failed';
}

/** Mirrors App\\Enums\\HistoryStatusFilter::statuses(). */
const statusesPerFilter: Record<HistoryStatusFilter, GenerationStatus[]> = {
    all: [
        'pending',
        'submission_failed',
        'queued',
        'processing',
        'succeeded',
        'failed',
    ],
    in_progress: ['pending', 'queued', 'processing'],
    succeeded: ['succeeded'],
    failed: ['failed', 'submission_failed'],
};

/**
 * Whether a generation pushed over the socket belongs in the list as it is filtered right now,
 * using the same rules as the server (status tab, case-insensitive prompt search).
 */
export function matchesHistoryFilters(
    generation: GenerationHistoryItem,
    filters: HistoryFilters,
): boolean {
    const matchesStatus = statusesPerFilter[filters.status].includes(
        generation.status,
    );
    const matchesSearch = generation.prompt
        .toLowerCase()
        .includes(filters.search.toLowerCase());

    return matchesStatus && matchesSearch;
}

/**
 * Failed generations are never charged; unfinished ones don't have a price yet.
 */
function formatCost(generation: GenerationHistoryItem): string {
    if (isFailedStatus(generation.status)) {
        return 'Not charged';
    }

    return generation.final_price_nano === null
        ? 'Pending'
        : formatMoney(generation.final_price_nano);
}

function formatEstimate(generation: GenerationHistoryItem): string {
    if (
        generation.estimated_min_nano === null ||
        generation.estimated_max_nano === null
    ) {
        return '—';
    }

    return formatMoneyRange(
        generation.estimated_min_nano,
        generation.estimated_max_nano,
    );
}

/**
 * Turns a generation from the server into the text a history card shows.
 */
export function toGenerationSummary(
    generation: GenerationHistoryItem,
): GenerationSummary {
    return {
        id: generation.id,
        prompt: generation.prompt,
        status: generation.status,
        statusLabel: statusLabels[generation.status],
        size: generation.size.replace('x', '×'),
        shape: shapeLabels[generation.size],
        quality: qualityLabels[generation.quality],
        imageUrl: generation.image_url ?? undefined,
        isExpired: generation.is_image_expired,
        errorMessage: generation.error?.message,
        errorCode: generation.error?.code,
        estimate: formatEstimate(generation),
        cost: formatCost(generation),
        createdAgo: formatTimeAgo(new Date(generation.created_at)),
        canDownload:
            generation.status === 'succeeded' && generation.image_url !== null,
    };
}
