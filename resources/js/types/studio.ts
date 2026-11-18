import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type GenerationStatus =
    | 'pending'
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

export type SizeOption = {
    ratio: string;
    dimensions: string;
    description: string;
    previewClass: string;
    isDefault?: boolean;
    isSelected?: boolean;
};

export type QualityOption = {
    label: string;
    description: string;
    isSelected?: boolean;
};

export type CostLineItem = {
    label: string;
    detail: string;
    amount: string;
    isEstimate?: boolean;
};

export type TopUpPreset = {
    amount: string;
    isSelected?: boolean;
};

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
