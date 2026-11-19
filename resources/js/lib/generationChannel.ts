/** The private channel App\Events\GenerationUpdated is broadcast on (see routes/channels.php). */
export function generationChannel(userId: number): string {
    return `users.${userId}.generations`;
}

/** Echo prefixes the App\Events namespace itself. */
export const GENERATION_UPDATED_EVENT = 'GenerationUpdated';
