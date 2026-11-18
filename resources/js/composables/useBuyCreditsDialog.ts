import type { Ref } from 'vue';
import { ref } from 'vue';

export type UseBuyCreditsDialogReturn = {
    isOpen: Ref<boolean>;
    openBuyCredits: () => void;
};

const isOpen = ref(false);

export function useBuyCreditsDialog(): UseBuyCreditsDialogReturn {
    function openBuyCredits() {
        isOpen.value = true;
    }

    return {
        isOpen,
        openBuyCredits,
    };
}
