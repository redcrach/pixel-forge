/**
 * After a failed submit, brings the first field with an error into view and focuses it,
 * so the error isn't missed when it is scrolled away (e.g. behind a phone's bottom action bar).
 *
 * Fields opt in with `data-field="<error key>"`, using the server's keys (`prompt`,
 * `reference_images.1`, …). The first one in page order that has an error wins.
 */
export function focusFirstInvalidField(
    errors: Partial<Record<string, string>>,
): void {
    const invalidField = [
        ...document.querySelectorAll<HTMLElement>('[data-field]'),
    ].find((field) => errors[field.dataset.field ?? ''] !== undefined);

    if (!invalidField) {
        return;
    }

    const focusTarget =
        invalidField.querySelector<HTMLElement>(
            'textarea, input, select, button:not([disabled])',
        ) ?? invalidField;

    // Centred, so a fixed bottom bar can't cover it; then focused without jumping again.
    focusTarget.scrollIntoView({ block: 'center', behavior: 'smooth' });
    focusTarget.focus({ preventScroll: true });
}
