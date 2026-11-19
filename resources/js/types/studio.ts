import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type GenerationStatus =
    | 'pending'
    | 'submission_failed'
    | 'queued'
    | 'processing'
    | 'succeeded'
    | 'failed';

export type StudioNavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
    badge?: string;
};

export type ImageSize = '1024x1024' | '1536x1024' | '1024x1536';

export type ImageQuality = 'low' | 'medium' | 'high';

export type SizeOption = {
    value: ImageSize;
    ratio: string;
    dimensions: string;
    description: string;
    previewClass: string;
    isDefault?: boolean;
};

export type QualityOption = {
    value: ImageQuality;
    label: string;
    description: string;
};

/** The fields posted to GenerationController (POST /generations). */
export type GenerateForm = {
    prompt: string;
    size: ImageSize;
    quality: ImageQuality;
    reference_images: string[];
};

/** One line of the estimate, in tokens and integer nanodollars. */
export type EstimateLine = {
    tokens_min: number;
    tokens_max: number;
    price_min_nano: number;
    price_max_nano: number;
};

/** Response of GenerationEstimateController (POST /generations/estimate). */
export type GenerationEstimate = {
    lines: {
        text: EstimateLine;
        reference_images: EstimateLine;
        output: EstimateLine;
        reasoning: EstimateLine;
    };
    total_min_nano: number;
    total_max_nano: number;
};

export type TopUpPreset = {
    amount: string;
    isSelected?: boolean;
};

/** A generation as GenerationResource sends it. Money is in integer nanodollars. */
export type GenerationHistoryItem = {
    id: string;
    prompt: string;
    status: GenerationStatus;
    size: ImageSize;
    quality: ImageQuality;
    reference_image_count: number;
    estimated_min_nano: number | null;
    estimated_max_nano: number | null;
    final_price_nano: number | null;
    image_url: string | null;
    is_image_expired: boolean;
    error: { code: string; message: string } | null;
    created_at: string;
    completed_at: string | null;
};

/** Payload of the GenerationUpdated broadcast. */
export type GenerationUpdatedPayload = {
    generation: GenerationHistoryItem;
};

/** Props of the History page (HistoryController). */
export type HistoryPageProps = {
    generations: { data: GenerationHistoryItem[] };
};

/** What a history card displays, already formatted. */
export type GenerationSummary = {
    id: string;
    prompt: string;
    status: GenerationStatus;
    statusLabel: string;
    size: string;
    quality: string;
    imageUrl?: string;
    isExpired?: boolean;
    errorMessage?: string;
    errorCode?: string;
    estimate: string;
    cost: string;
    createdAgo: string;
};

export type CostBreakdownRow = {
    phase: string;
    estimated: string;
    actual: string;
};

export type TimelineEvent = {
    title: string;
    description?: string;
    timestamp: string;
    tone: 'muted' | 'processing' | 'succeeded' | 'primary';
};

export type GenerationDetails = {
    id: string;
    status: GenerationStatus;
    statusLabel: string;
    createdAt: string;
    imageUrl: string;
    imageMeta: string;
    prompt: string;
    settings: string;
    costRows: CostBreakdownRow[];
    totalEstimated: string;
    totalActual: string;
    exactCharge: string;
    releasedHold: string;
    timeline: TimelineEvent[];
};

export type HistoryFilterTab = {
    label: string;
    count: number;
    isActive?: boolean;
};
